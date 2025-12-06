<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffInventoryMovement;
use App\Models\ffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderActionMail;

class FfInventoryController extends Controller
{
    public function index()
    {
        $products = ffProduct::withSum('movements', 'quantity')
                        ->orderBy('description')
                        ->get();
        
        $products->each(function ($product) {
            $product->description = str_replace(["\n", "\r", "\t"], ' ', $product->description);
        });

        $brands = ffProduct::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        $types = ffProduct::whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
        
        return view('friends-and-family.inventory.index', compact('products', 'brands', 'types'));
    }

    public function storeMovement(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->is_area_admin) {
            abort(403, 'Acción no autorizada.');
        }

        $request->validate([
            'product_id' => 'required|exists:ff_products,id',
            'quantity'   => 'required|integer|not_in:0',
            'reason'     => 'required|string|max:255',
        ]);

        $movement = ffInventoryMovement::create([
            'ff_product_id' => $request->product_id,
            'user_id'       => Auth::id(),
            'quantity'      => $request->quantity,
            'reason'        => $request->reason,
        ]);

        $newTotal = ffInventoryMovement::where('ff_product_id', $request->product_id)
                        ->sum('quantity');

        return response()->json([
            'message'       => 'Movimiento registrado con éxito.',
            'product_id'    => $movement->ff_product_id,
            'new_total'     => $newTotal,
        ]);
    }

    public function logIndex()
    {
        $movements = ffInventoryMovement::with('product', 'user')
                        ->orderBy('created_at', 'desc')
                        ->paginate(50);
        
        return view('friends-and-family.inventory.log', compact('movements'));
    }

    public function exportCsv(Request $request)
    {
        $query = ffProduct::withSum('movements', 'quantity');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $products = $query->orderBy('description')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ff_inventario_'.date('Y-m-d').'.csv"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['SKU', 'UPC', 'Descripcion', 'Marca', 'Tipo', 'Precio Unitario', 'Pzas/Caja', 'Cantidad Actual']);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->sku,
                    $product->upc,
                    $product->description,
                    $product->brand,
                    $product->type,
                    $product->unit_price,
                    $product->pieces_per_box,
                    $product->movements_sum_quantity ?? 0,
                ]);
            }
            fclose($file);
        };
        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportLogCsv()
    {
        $movements = ffInventoryMovement::with('product', 'user')->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ff_log_movimientos_'.date('Y-m-d').'.csv"',
        ];

        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Fecha', 'Usuario', 'SKU', 'Producto', 'Movimiento', 'Motivo']);
            foreach ($movements as $mov) {
                fputcsv($file, [
                    $mov->created_at->format('Y-m-d H:i:s'),
                    $mov->user ? $mov->user->name : 'N/A',
                    $mov->product ? $mov->product->sku : 'N/A',
                    $mov->product ? $mov->product->description : 'N/A',
                    $mov->quantity,
                    $mov->reason,
                ]);
            }
            fclose($file);
        };
        return new StreamedResponse($callback, 200, $headers);
    }

    public function importMovements(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()?->is_area_admin) {
            abort(403, 'Acción no autorizada.');
        }

        $request->validate([
            'movements_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('movements_file')->getPathname();
        
        $errors = [];
        $processedCount = 0;
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            if (($handle = fopen($path, 'r')) === FALSE) {
                throw new \Exception("No se pudo abrir el archivo CSV.");
            }

            fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== FALSE) {
                $rowNumber++;

                $sku = mb_convert_encoding(trim($row[0] ?? ''), 'UTF-8', 'ISO-8859-1');
                $quantity = mb_convert_encoding(trim($row[1] ?? ''), 'UTF-8', 'ISO-8859-1');
                $reason = mb_convert_encoding(trim($row[2] ?? ''), 'UTF-8', 'ISO-8859-1');

                if (empty($sku) && empty($quantity) && empty($reason)) {
                    continue;
                }

                $product = ffProduct::where('sku', $sku)->first();
                if (!$product) {
                    $errors[] = "Línea $rowNumber: El SKU '$sku' no fue encontrado.";
                    continue;
                }

                if (!is_numeric($quantity) || $quantity == 0) {
                    $errors[] = "Línea $rowNumber: La cantidad '$quantity' para el SKU '$sku' no es un número válido o es 0.";
                    continue;
                }
                
                if (empty($reason)) {
                    $errors[] = "Línea $rowNumber: El motivo es obligatorio para el SKU '$sku'.";
                    continue;
                }

                ffInventoryMovement::create([
                    'ff_product_id' => $product->id,
                    'user_id'       => Auth::id(),
                    'quantity'      => (int)$quantity,
                    'reason'        => $reason . " (Importación CSV)",
                ]);

                $processedCount++;
            }
            fclose($handle);

            if (!empty($errors)) {
                DB::rollBack();
                return redirect()->route('ff.inventory.index')
                    ->with('import_errors', $errors)
                    ->with('error_summary', "La importación falló. Se encontraron " . count($errors) . " errores. Ningún movimiento fue registrado.");
            }

            DB::commit();

            return redirect()->route('ff.inventory.index')
                ->with('success', "¡Éxito! Se importaron $processedCount movimientos de inventario correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en importación de movimientos F&F: " . $e->getMessage());
            $errors[] = "Error inesperado del sistema: " . $e->getMessage();
            return redirect()->route('ff.inventory.index')
                ->with('import_errors', $errors)
                ->with('error_summary', "Ocurrió un error crítico durante la importación. Ningún movimiento fue registrado.");
        }
    }

    public function downloadMovementTemplate(Request $request)
    {
        $query = ffProduct::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $products = $query->orderBy('sku')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_movimientos_ff.csv"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['SKU', 'Quantity', 'Reason', 'Description (For Reference Only)']);
            
            if ($products->isEmpty()) {
                fputcsv($file, [
                    'SKU-EJEMPLO-1', 
                    '10', 
                    'Ajuste de stock inicial',
                    'Ejemplo de entrada'
                ]);
                fputcsv($file, [
                    'SKU-EJEMPLO-2', 
                    '-2', 
                    'Producto dañado o merma',
                    'Ejemplo de salida'
                ]);
            } else {
                foreach ($products as $product) {
                    fputcsv($file, [
                        $product->sku,
                        '',
                        '',
                        $product->description
                    ]);
                }
            }

            fclose($file);
        };
        return new StreamedResponse($callback, 200, $headers);
    }

    public function backorders()
    {
        $backorders = ffInventoryMovement::where('is_backorder', true)
            ->where('backorder_fulfilled', false)
            ->with(['product', 'user']) 
            ->orderBy('created_at', 'asc') 
            ->get()
            ->groupBy('ff_product_id');
        return view('friends-and-family.inventory.backorders', compact('backorders'));
    }

    public function fulfillBackorder(Request $request)
    {
        $request->validate(['movement_id' => 'required|exists:ff_inventory_movements,id']);
        
        $movement = ffInventoryMovement::with(['product', 'user'])->find($request->movement_id);
        $product = $movement->product;
        
        $currentStock = $product->movements()->sum('quantity');
        $required = abs($movement->quantity);

        if ($currentStock < $required) {
            return response()->json(['message' => "Stock insuficiente. Tienes $currentStock, necesitas $required."], 422);
        }

        $movement->update([
            'backorder_fulfilled' => true,
            'observations' => $movement->observations . " [SURTIDO EL " . date('d/m/Y') . "]",
        ]);

        if ($movement->user && $movement->user->email) {
            try {
                $logoUrl = Storage::disk('s3')->url('logoConsorcioMonter.png');
                
                $mailData = [
                    'folio' => $movement->folio,
                    'client_name' => $movement->client_name,
                    'company_name' => $movement->company_name,
                    'delivery_date' => $movement->delivery_date ? $movement->delivery_date->format('d/m/Y') : 'N/A',
                    'vendedor_name' => $movement->user->name,
                    'logo_url' => $logoUrl,
                    'items' => [
                        [
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'quantity' => abs($movement->quantity)
                        ]
                    ]
                ];

                Mail::to($movement->user->email)->send(new OrderActionMail($mailData, 'backorder_filled'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error enviando email backorder: " . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Pedido marcado como surtido y vendedor notificado.']);
    }
    
}