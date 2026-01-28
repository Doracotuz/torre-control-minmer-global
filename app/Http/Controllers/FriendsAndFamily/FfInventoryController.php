<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffInventoryMovement;
use App\Models\ffProduct;
use App\Models\FfWarehouse;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderActionMail;
use Illuminate\Validation\Rule;

class FfInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ffProduct::query();

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

        $warehouseId = $request->input('warehouse_id');

        $products = $query->withSum(['movements' => function ($q) use ($warehouseId) {
            if ($warehouseId) {
                $q->where('ff_warehouse_id', $warehouseId);
            }
        }], 'quantity')
        ->orderBy('description')
        ->get();
        
        $products->each(function ($product) {
            $product->description = str_replace(["\n", "\r", "\t"], ' ', $product->description);
        });

        $brandsQuery = ffProduct::whereNotNull('brand')->where('brand', '!=', '');
        $typesQuery = ffProduct::whereNotNull('type')->where('type', '!=', '');
        $warehousesQuery = FfWarehouse::where('is_active', true);

        if (!Auth::user()->isSuperAdmin()) {
            $brandsQuery->where('area_id', Auth::user()->area_id);
            $typesQuery->where('area_id', Auth::user()->area_id);
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $warehousesQuery->where('area_id', $request->input('area_id'));
        }

        $brands = $brandsQuery->distinct()->orderBy('brand')->pluck('brand');
        $types = $typesQuery->distinct()->orderBy('type')->pluck('type');
        $warehouses = $warehousesQuery->orderBy('description')->get();
        
        return view('friends-and-family.inventory.index', compact('products', 'brands', 'types', 'warehouses'));
    }

    public function storeMovement(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->is_area_admin) {
            abort(403, 'Acción no autorizada.');
        }

        $targetAreaId = Auth::user()->area_id;

        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('ff_products', 'id')->where(function ($query) use ($targetAreaId) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where('area_id', $targetAreaId);
                    }
                }),
            ],
            'quantity' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
            'warehouse_id' => [
                'nullable',
                Rule::exists('ff_warehouses', 'id')->where(function ($query) use ($targetAreaId) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where('area_id', $targetAreaId);
                    }
                })
            ],
        ]);

        $product = ffProduct::findOrFail($request->product_id);

        if (!Auth::user()->isSuperAdmin() && $product->area_id !== Auth::user()->area_id) {
            abort(403, 'Este producto no pertenece a tu área.');
        }

        $movement = ffInventoryMovement::create([
            'ff_product_id' => $product->id,
            'user_id' => Auth::id(),
            'area_id' => $product->area_id,
            'quantity' => $request->quantity,
            'reason' => $request->reason,
            'ff_warehouse_id' => $request->input('warehouse_id'),
        ]);

        $newTotalQuery = ffInventoryMovement::where('ff_product_id', $request->product_id);
        
        if ($request->filled('warehouse_id')) {
            $newTotalQuery->where('ff_warehouse_id', $request->input('warehouse_id'));
        }

        $newTotal = $newTotalQuery->sum('quantity');

        return response()->json([
            'message' => 'Movimiento registrado con éxito.',
            'product_id' => $movement->ff_product_id,
            'new_total' => $newTotal,
        ]);
    }

    public function logIndex(Request $request)
    {
        $query = ffInventoryMovement::with(['product', 'user', 'warehouse'])->orderBy('created_at', 'desc');
        
        $areas = [];
        $currentArea = '';
        $warehousesQuery = FfWarehouse::where('is_active', true);

        if (Auth::user()->isSuperAdmin()) {
            $areas = Area::all(); 
            
            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
                $warehousesQuery->where('area_id', $request->area_id);
                $currentArea = $request->area_id;
            }
        } else {
            $query->where('area_id', Auth::user()->area_id);
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('ff_warehouse_id', $request->warehouse_id);
        }

        $warehouses = $warehousesQuery->orderBy('description')->get();
        $movements = $query->paginate(50);
        
        return view('friends-and-family.inventory.log', compact('movements', 'areas', 'currentArea', 'warehouses'));
    }

    public function exportCsv(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');

        $query = ffProduct::withSum(['movements' => function ($q) use ($warehouseId) {
            if ($warehouseId) {
                $q->where('ff_warehouse_id', $warehouseId);
            }
        }], 'quantity');

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }        

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
        
        $warehouseName = $warehouseId ? (FfWarehouse::find($warehouseId)->description ?? 'Almacen_Desconocido') : 'Global';
        $filename = 'ff_inventario_' . $warehouseName . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
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

    public function exportLogCsv(Request $request)
    {
        $query = ffInventoryMovement::with(['product', 'user', 'warehouse'])->orderBy('created_at', 'desc');

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('ff_warehouse_id', $request->input('warehouse_id'));
        }

        $movements = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ff_log_movimientos_'.date('Y-m-d').'.csv"',
        ];

        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Fecha', 'Usuario', 'Almacen', 'SKU', 'Producto', 'Movimiento', 'Motivo']);
            foreach ($movements as $mov) {
                fputcsv($file, [
                    $mov->created_at->format('Y-m-d H:i:s'),
                    $mov->user ? $mov->user->name : 'N/A',
                    $mov->warehouse ? ($mov->warehouse->code . ' - ' . $mov->warehouse->description) : 'General',
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
        $targetAreaId = Auth::user()->area_id;

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $targetAreaId = $request->input('area_id');
        }

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
                $warehouseCode = mb_convert_encoding(trim($row[4] ?? ''), 'UTF-8', 'ISO-8859-1');

                if (empty($sku) && empty($quantity) && empty($reason)) {
                    continue;
                }

                $query = ffProduct::where('sku', $sku);
                if (!Auth::user()->isSuperAdmin()) {
                    $query->where('area_id', $targetAreaId);
                }
                $product = $query->first();

                if (!$product) {
                    $errors[] = "Línea $rowNumber: El SKU '$sku' no fue encontrado o no pertenece a tu área.";
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

                $warehouseId = null;
                if (!empty($warehouseCode)) {
                    $warehouse = FfWarehouse::where('code', $warehouseCode)
                        ->where('area_id', $targetAreaId)
                        ->first();
                    
                    if ($warehouse) {
                        $warehouseId = $warehouse->id;
                    } else {
                        $errors[] = "Línea $rowNumber: El código de almacén '$warehouseCode' no existe en tu área.";
                        continue;
                    }
                }

                ffInventoryMovement::create([
                    'ff_product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'area_id' => $product->area_id,
                    'quantity' => (int)$quantity,
                    'reason' => $reason . " (Importación CSV)",
                    'ff_warehouse_id' => $warehouseId,
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
            Log::error("Error en importación de movimientos: " . $e->getMessage());
            $errors[] = "Error inesperado del sistema: " . $e->getMessage();
            return redirect()->route('ff.inventory.index')
                ->with('import_errors', $errors)
                ->with('error_summary', "Ocurrió un error crítico durante la importación. Ningún movimiento fue registrado.");
        }
    }

    public function downloadMovementTemplate(Request $request)
    {
        $query = ffProduct::query();

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

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
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, ['SKU', 'Quantity', 'Reason', 'Description (Reference)', 'Warehouse Code (Optional)']);
            
            if ($products->isEmpty()) {
                fputcsv($file, [
                    'SKU-EJEMPLO-1', 
                    '10', 
                    'Ajuste de stock inicial',
                    'Ejemplo de entrada',
                    'ALM-01'
                ]);
                fputcsv($file, [
                    'SKU-EJEMPLO-2', 
                    '-2', 
                    'Producto dañado',
                    'Ejemplo de salida',
                    ''
                ]);
            } else {
                foreach ($products as $product) {
                    fputcsv($file, [
                        $product->sku,
                        '',
                        '',
                        $product->description,
                        ''
                    ]);
                }
            }

            fclose($file);
        };
        return new StreamedResponse($callback, 200, $headers);
    }

    public function backorders(Request $request)
    {
        $query = ffInventoryMovement::where('is_backorder', true)
            ->where('backorder_fulfilled', false);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('ff_warehouse_id', $request->input('warehouse_id'));
        }

        $backorders = $query->with(['product', 'user', 'warehouse']) 
            ->orderBy('created_at', 'asc') 
            ->get()
            ->groupBy('ff_product_id');

        $warehousesQuery = FfWarehouse::where('is_active', true);
        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        }
        $warehouses = $warehousesQuery->orderBy('description')->get();
            
        return view('friends-and-family.inventory.backorders', compact('backorders', 'warehouses'));
    }

    public function fulfillBackorder(Request $request)
    {
        $request->validate(['movement_id' => 'required|exists:ff_inventory_movements,id']);
        
        $movement = ffInventoryMovement::with(['product', 'user'])->find($request->movement_id);

        if (!Auth::user()->isSuperAdmin() && $movement->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para gestionar este backorder.');
        }

        $product = $movement->product;
        
        $stockQuery = $product->movements();
        if ($movement->ff_warehouse_id) {
            $stockQuery->where('ff_warehouse_id', $movement->ff_warehouse_id);
        }

        $currentStock = $stockQuery->sum('quantity');
        $required = abs($movement->quantity);

        if ($currentStock < $required) {
            $msg = "Stock insuficiente";
            if ($movement->ff_warehouse_id) {
                $wh = FfWarehouse::find($movement->ff_warehouse_id);
                $msg .= " en almacén " . ($wh ? $wh->code : 'Desconocido');
            }
            return response()->json(['message' => "$msg. Tienes $currentStock, necesitas $required."], 422);
        }

        $movement->update([
            'backorder_fulfilled' => true,
            'observations' => $movement->observations . " [SURTIDO EL " . date('d/m/Y') . "]",
        ]);

        if ($movement->user && $movement->user->email) {
            try {
                $area = Area::find($movement->area_id);
                
                $logoPath = ($area && $area->icon_path) ? $area->icon_path : 'LogoAzulm.PNG';
                $logoUrl = Storage::disk('s3')->url($logoPath);
                
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
                Log::error("Error enviando email backorder: " . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Pedido marcado como surtido y vendedor notificado.']);
    }

    public function backorderRelations(Request $request)
    {
        $productsQuery = ffProduct::query();

        if (!Auth::user()->isSuperAdmin()) {
            $productsQuery->where('area_id', Auth::user()->area_id);
        }
        
        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $productsQuery->where('area_id', $request->input('area_id'));
        }

        $warehouseId = $request->input('warehouse_id');

        $products = $productsQuery->whereHas('movements', function($q) use ($warehouseId) {
            $q->where('is_backorder', true)
              ->where('backorder_fulfilled', false);
            
            if ($warehouseId) {
                $q->where('ff_warehouse_id', $warehouseId);
            }
        })
        ->with(['movements' => function($q) use ($warehouseId) {
            $q->where('is_backorder', true)
              ->where('backorder_fulfilled', false)
              ->orderBy('created_at', 'asc');
            
            if ($warehouseId) {
                $q->where('ff_warehouse_id', $warehouseId);
            }
        }])
        ->get()
        ->map(function($product) {
            $product->total_debt = $product->movements->sum(fn($m) => abs($m->quantity));
            return $product;
        });

        $warehousesQuery = FfWarehouse::where('is_active', true);
        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        }
        $warehouses = $warehousesQuery->orderBy('description')->get();

        return view('friends-and-family.inventory.backorder-relations', compact('products', 'warehouses'));
    }

    public function resolveBackorder(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'warehouse_id' => 'required|exists:ff_warehouses,id',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $product = ffProduct::findOrFail($id);
                $quantityAvailable = $request->quantity;

                $folioNumerico = time(); 

                ffInventoryMovement::create([
                    'ff_product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'ff_warehouse_id' => $request->warehouse_id,
                    'area_id' => $product->area_id,
                    'quantity' => $quantityAvailable,
                    'reason' => 'Resolución de Backorder (Ingreso Directo)',
                    'folio' => $folioNumerico,
                    'order_type' => 'entrada',
                    'client_name' => 'Ajuste de Inventario',
                ]);

                $pendingBackorders = ffInventoryMovement::where('ff_product_id', $product->id)
                    ->where('is_backorder', true)
                    ->where('backorder_fulfilled', false)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($pendingBackorders as $backorder) {
                    if ($quantityAvailable <= 0) break;

                    $debt = abs($backorder->quantity);

                    if ($quantityAvailable >= $debt) {
                        $backorder->update([
                            'backorder_fulfilled' => true,
                            'was_edited' => true
                        ]);
                        $quantityAvailable -= $debt;

                    } else {
                        $remanente = $debt - $quantityAvailable;
                        
                        $backorder->update([
                            'quantity' => -$remanente,
                            'was_edited' => true
                        ]);

                        $fulfilledPart = $backorder->replicate();
                        $fulfilledPart->quantity = -$quantityAvailable;
                        $fulfilledPart->backorder_fulfilled = true;
                        $fulfilledPart->observations .= " (Surtido Parcial)";
                        $fulfilledPart->created_at = $backorder->created_at;
                        $fulfilledPart->save();

                        $quantityAvailable = 0; 
                    }
                }
            });

            return redirect()->back()->with('success', 'Mercancía ingresada. Las deudas se han ajustado y recalculado correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al procesar el ingreso: ' . $e->getMessage());
        }
    }

}