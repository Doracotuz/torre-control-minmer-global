<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffCartItem;
use App\Models\ffInventoryMovement;
use App\Models\ffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $user = Auth::user();

        $request->validate([
            'client_name'   => 'required|string|max:255',
            'surtidor_name' => 'nullable|string|max:255',
        ]);

        $cartItems = ffCartItem::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 400);
        }

        $ventaFolio = $this->getNextFolio();
        $pdfItems = [];
        $grandTotal = 0;

        DB::beginTransaction();

        try {
            foreach ($cartItems as $item) {
                $product = $item->product;
                $quantity = $item->quantity;
                $price = $product->price ?? 0;
                $totalPrice = $quantity * $price;
                
                ffInventoryMovement::create([
                    'ff_product_id' => $product->id,
                    'user_id'       => $user->id,
                    'quantity'      => -$quantity,
                    'reason'        => 'Venta F&F Folio ' . $ventaFolio,
                    'client_name'   => $request->client_name,
                    'surtidor_name' => $request->surtidor_name,
                    'folio'         => $ventaFolio,
                ]);

                $pdfItems[] = [
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $totalPrice,
                ];

                $grandTotal += $totalPrice;
                
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
        
        $logoUrl = Storage::disk('s3')->url('logoMoetHennessy.PNG');

        $pdfData = [
            'items' => $pdfItems,
            'grandTotal' => $grandTotal,
            'folio' => $ventaFolio,
            'date' => now()->format('d/m/Y H:i:s'),
            'client_name' => $request->client_name,
            'surtidor_name' => $request->surtidor_name,
            'vendedor_name' => $user->name,
            'logo_url' => $logoUrl,
            'copies' => ['Original', 'Copia Cliente', 'Copia Almacén'],
        ];

        $pdfView = view('friends-and-family.sales.pdf', $pdfData);
        $dompdf = new Dompdf();
        
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

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
            'date'     => now()->format('d/m/Y'),
        ];
        
        $pdfData['logo_url'] = Storage::disk('s3')->url('logoMoetHennessy.PNG');
        

        $pdfView = view('friends-and-family.sales.print-pdf', $pdfData);
        $dompdf = new Dompdf();
        
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

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