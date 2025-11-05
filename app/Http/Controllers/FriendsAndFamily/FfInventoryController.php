<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffInventoryMovement;
use App\Models\ffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        
        return view('friends-and-family.inventory.index', compact('products'));
    }

    public function storeMovement(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
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

    public function exportCsv()
    {
        $products = ffProduct::withSum('movements', 'quantity')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ff_inventario_'.date('Y-m-d').'.csv"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['SKU', 'Descripcion', 'Marca', 'Tipo', 'Precio', 'Cantidad Actual']);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->sku,
                    $product->description,
                    $product->brand,
                    $product->type,
                    $product->price,
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
                    $mov->user ? $mov->user->name : 'N/A', //
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
}