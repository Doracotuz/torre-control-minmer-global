<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffCartItem;
use App\Models\ffInventoryMovement;
use App\Models\ffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;

class FfSalesController extends Controller
{

    private function getNextFolio(): int
    {
        $lastMovement = ffInventoryMovement::orderByDesc('folio')->first();
        return ($lastMovement ? $lastMovement->folio : 0) + 1;
    }

    public function index()
    {
        $userId = Auth::id();

        $products = ffProduct::where('is_active', true)
            ->withSum('movements', 'quantity')
            ->withSum(['cartItems as reserved_by_others' => function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            }], 'quantity')
            ->with(['cartItems' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderBy('description')
            ->get();

        $nextFolio = $this->getNextFolio();            
        
        return view('friends-and-family.sales.index', compact('products', 'nextFolio'));
    }

    public function updateCartItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:ff_products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;
        $userId = Auth::id();

        if ($quantity <= 0) {
            ffCartItem::where('user_id', $userId)->where('ff_product_id', $productId)->delete();
            return response()->json(['message' => 'Producto liberado']);
        }

        $product = ffProduct::find($productId);
        $totalStock = $product->movements()->sum('quantity');
        $reservedByOthers = $product->cartItems()->where('user_id', '!=', $userId)->sum('quantity');
        $available = $totalStock - $reservedByOthers;

        if ($quantity > $available) {
            return response()->json([
                'message' => 'Stock insuficiente. Solo quedan ' . $available . ' disponibles.'
            ], 422);
        }

        ffCartItem::updateOrCreate(
            ['user_id' => $userId, 'ff_product_id' => $productId],
            ['quantity' => $quantity]
        );

        return response()->json(['message' => 'Producto comprometido']);
    }

    public function getReservations()
    {
        $userId = Auth::id();
        $reservations = ffCartItem::where('user_id', '!=', $userId)
            ->groupBy('ff_product_id')
            ->select('ff_product_id', DB::raw('SUM(quantity) as reserved_quantity'))
            ->pluck('reserved_quantity', 'ff_product_id');
        
        return response()->json($reservations);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'client_name'   => 'required|string|max:255', 
            'surtidor_name' => 'required|string|max:255', 
        ]);

        $user = Auth::user();
        $userId = $user->id;

        $cartItems = ffCartItem::where('user_id', $userId)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 400);
        }

        DB::beginTransaction();
        try {

            $lastMovement = ffInventoryMovement::orderByDesc('folio')->lockForUpdate()->first();
            $ventaFolio = ($lastMovement ? $lastMovement->folio : 0) + 1;

            $pdfData = [
                'items' => [],
                'grandTotal' => 0,
                'copies' => ['CLIENTE', 'VENDEDOR', 'AUDITOR']
            ];

            foreach ($cartItems as $item) {
                $product = $item->product;

                $product->lockForUpdate();
                
                $currentStock = ffInventoryMovement::where('ff_product_id', $product->id)->sum('quantity');
                
                $reservedByOthers = ffCartItem::where('ff_product_id', $product->id)
                    ->where('user_id', '!=', $userId)
                    ->sum('quantity');
                    
                $availableStock = $currentStock - $reservedByOthers;

                if ($item->quantity > $availableStock) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Stock insuficiente para ' . $product->sku . '. Disponible: ' . $availableStock
                    ], 400);
                }

                ffInventoryMovement::create([
                    'ff_product_id' => $product->id,
                    'user_id' => $userId,
                    'quantity' => -$item->quantity,
                    'reason' => 'Venta Friends & Family',
                    'client_name' => $request->client_name,
                    'surtidor_name' => $request->surtidor_name,
                    'folio' => $ventaFolio,
                ]);

                $totalItem = $product->price * $item->quantity;
                $pdfData['items'][] = [
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $product->price,
                    'total_price' => $totalItem,
                ];
                $pdfData['grandTotal'] += $totalItem;
            }

            ffCartItem::where('user_id', $userId)->delete();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
        
        $pdfData['date'] = now()->format('d/m/Y H:i:s');
        $pdfData['client_name'] = $request->client_name;
        $pdfData['surtidor_name'] = $request->surtidor_name;
        $pdfData['vendedor_name'] = $user->name;
        $pdfData['folio'] = $ventaFolio;

        $pdfView = view('friends-and-family.sales.pdf', $pdfData);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($pdfView->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return response(
            $dompdf->output(), 
            200, 
            ['Content-Type' => 'application/pdf','X-Venta-Folio' => $ventaFolio]
        );
    }

    public function printList(Request $request)
    {
        $data = $request->validate([
            'products' => 'required|array',
            'numSets'  => 'required|integer|min:1',
        ]);

        $pdfData = [
            'products' => $data['products'],
            'numSets'  => $data['numSets'],
            'date'     => now()->format('d/m/Y')
        ];

        $pdfView = view('friends-and-family.sales.print-pdf', $pdfData);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($pdfView->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return response(
            $dompdf->output(), 
            200, 
            ['Content-Type' => 'application/pdf']
        );
    }

}