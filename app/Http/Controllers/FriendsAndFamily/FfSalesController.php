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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewSaleMail;
use App\Mail\OrderActionMail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\FfOrderDocument;
use Illuminate\Http\UploadedFile;

class FfSalesController extends Controller
{
    private function getNextFolio(): int
    {
        $lastMovement = ffInventoryMovement::orderByDesc('folio')->first();
        return ($lastMovement ? $lastMovement->folio : 10000) + 1;
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
            'folio' => 'nullable|integer'
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;
        $userId = Auth::id();
        $editingFolio = $request->folio;

        if ($quantity <= 0) {
            ffCartItem::where('user_id', $userId)->where('ff_product_id', $productId)->delete();
            return response()->json(['message' => 'Producto liberado']);
        }

        $product = ffProduct::find($productId);
        
        $query = $product->movements();
        
        if ($editingFolio) {
            $query->where('folio', '!=', $editingFolio);
        }
        
        $totalStock = $query->sum('quantity'); 

        $reservedByOthers = $product->cartItems()->where('user_id', '!=', $userId)->sum('quantity');
        $available = $totalStock - $reservedByOthers;

        if ($quantity > $available) {
            return response()->json([
                'message' => 'Stock insuficiente. Solo quedan ' . $available . ' disponibles (incluyendo lo de este pedido).'
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

    public function searchOrder(Request $request)
    {
        $request->validate(['folio' => 'required|integer']);
        
        $movements = ffInventoryMovement::where('folio', $request->folio)
            ->with('product')
            ->orderBy('created_at', 'desc') 
            ->get();

        if ($movements->isEmpty()) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        $header = $movements->first();
        $user = Auth::user();

        ffCartItem::where('user_id', $user->id)->delete();

        $cartItemsData = [];

        $groupedProducts = $movements->groupBy('ff_product_id');

        foreach($groupedProducts as $productId => $productMovements) {
            $netQuantity = $productMovements->sum('quantity');

            if ($netQuantity < 0) {
                $finalQty = abs($netQuantity);
                
                ffCartItem::create([
                    'user_id' => $user->id,
                    'ff_product_id' => $productId,
                    'quantity' => $finalQty
                ]);

                $cartItemsData[] = [
                    'product_id' => $productId,
                    'quantity' => $finalQty
                ];
            }
        }

        $documents = [];
        try {
            if (class_exists(\App\Models\FfOrderDocument::class)) {
                $documents = \App\Models\FfOrderDocument::where('folio', $request->folio)
                    ->get()
                    ->map(function($doc) {
                        return [
                            'id' => $doc->id,
                            'name' => $doc->filename,
                            'url' => $doc->url, 
                            'is_existing' => true
                        ];
                    });
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error cargando documentos: " . $e->getMessage());
        }

        return response()->json([
            'client_data' => [
                'client_name' => $header->client_name,
                'company_name' => $header->company_name,
                'client_phone' => $header->client_phone,
                'address' => $header->address,
                'locality' => $header->locality,
                'delivery_date' => $header->delivery_date ? $header->delivery_date->format('Y-m-d\TH:i') : '',
                'surtidor_name' => $header->surtidor_name,
                'observations' => $header->observations,
                'folio' => $header->folio,
            ],
            'cart_items' => $cartItemsData,
            'documents' => $documents,
            'message' => 'Pedido cargado para edición.'
        ]);
    }

    public function cancelOrder(Request $request)
    {
        $request->validate([
            'folio' => 'required|integer|exists:ff_inventory_movements,folio',
            'reason' => 'required|string'
        ]);

        $user = Auth::user();
        $folio = $request->folio;

        DB::beginTransaction();
        try {
            $originalMovements = ffInventoryMovement::where('folio', $folio)
                ->where('quantity', '<', 0)
                ->with('product')
                ->get();

            if ($originalMovements->isEmpty()) {
                throw new \Exception("El pedido ya fue cancelado o no existe.");
            }

            $header = $originalMovements->first();
            $emailRecipients = [];
            if ($request->filled('email_recipients')) {
                 $emailRecipients = explode(';', $request->email_recipients);
            }

            foreach($originalMovements as $mov) {
                ffInventoryMovement::create([
                    'ff_product_id' => $mov->ff_product_id,
                    'user_id' => $user->id,
                    'quantity' => abs($mov->quantity),
                    'reason' => 'CANCELACIÓN Venta Folio ' . $folio . ': ' . $request->reason,
                    'client_name' => $mov->client_name,
                    'folio' => $folio
                ]);
            }

            ffCartItem::where('user_id', $user->id)->delete();

            DB::commit();

            if (!empty($emailRecipients)) {
                $mailData = [
                    'folio' => $folio,
                    'client_name' => $header->client_name,
                    'company_name' => $header->company_name,
                    'delivery_date' => $header->delivery_date,
                    'surtidor_name' => $header->surtidor_name,
                    'cancel_reason' => $request->reason,
                    'items' => []
                ];
                
                try {
                    Mail::to($emailRecipients)->send(new OrderActionMail($mailData, 'cancel'));
                } catch (\Exception $e) { \Illuminate\Support\Facades\Log::error("Error mail cancel: ".$e->getMessage()); }
            }

            return response()->json(['message' => 'Pedido cancelado y stock restaurado.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        
        $isEditMode = filter_var($request->input('is_edit_mode'), FILTER_VALIDATE_BOOLEAN);

        $request->validate([
            'folio'         => $isEditMode 
                                ? 'required|integer|exists:ff_inventory_movements,folio'
                                : 'required|integer|unique:ff_inventory_movements,folio',
            'client_name'   => 'required|string|max:255',
            'company_name'  => 'required|string|max:255',
            'client_phone'  => 'required|string|max:50',
            'address'       => 'required|string',
            'locality'      => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'surtidor_name' => 'nullable|string|max:255',
            'observations'  => 'nullable|string',
            'email_recipients' => 'nullable|string',
            'documents'     => 'array|max:5',
            'documents.*'   => 'file|mimes:pdf|max:10240',
        ], [
            'folio.unique' => 'El folio ya existe.',
            'folio.exists' => 'El folio a editar no existe.',
            'documents.max' => 'Máximo 5 documentos permitidos.',
            'documents.*.mimes' => 'Solo se permiten archivos PDF.',
            'documents.*.max' => 'Cada archivo debe pesar menos de 10MB.'
        ]);

        $cartItems = ffCartItem::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 400);
        }

        $ventaFolio = $request->folio;
        $pdfItems = [];
        $grandTotal = 0;

        DB::beginTransaction();

        try {
            if ($isEditMode) {
                $originalMovements = ffInventoryMovement::where('folio', $ventaFolio)
                    ->where('quantity', '<', 0)
                    ->get();
                
                foreach($originalMovements as $mov) {
                    ffInventoryMovement::create([
                        'ff_product_id' => $mov->ff_product_id,
                        'user_id' => $user->id,
                        'quantity' => abs($mov->quantity),
                        'reason' => 'Ajuste por Edición Folio ' . $ventaFolio,
                        'folio' => $ventaFolio
                    ]);
                }
            }

            foreach ($cartItems as $item) {
                $product = $item->product;
                $quantity = $item->quantity;
                $price = $product->unit_price ?? 0;
                $totalPrice = $quantity * $price;
                
                ffInventoryMovement::create([
                    'ff_product_id' => $product->id,
                    'user_id'       => $user->id,
                    'quantity'      => -$quantity,
                    'reason'        => ($isEditMode ? 'Edición' : 'Venta') . ' F&F Folio ' . $ventaFolio,
                    'client_name'   => $request->client_name,
                    'company_name'  => $request->company_name,
                    'client_phone'  => $request->client_phone,
                    'address'       => $request->address,
                    'locality'      => $request->locality,
                    'delivery_date' => $request->delivery_date,
                    'surtidor_name' => $request->surtidor_name,
                    'observations'  => $request->observations,
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

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->storeAs(
                        "ff_order_documents/{$ventaFolio}", 
                        $file->getClientOriginalName(), 
                        's3'
                    );
                    
                    FfOrderDocument::create([
                        'folio' => $ventaFolio,
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path
                    ]);
                }
            }

            DB::commit();
            ffCartItem::where('user_id', $user->id)->delete();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
        
        $logoUrl = Storage::disk('s3')->url('logoConsorcioMonter.png');
        $pdfData = [
            'items' => $pdfItems,
            'grandTotal' => $grandTotal,
            'folio' => $ventaFolio,
            'date' => now()->format('d/m/Y'),
            'client_name' => $request->client_name,
            'company_name' => $request->company_name,
            'client_phone' => $request->client_phone,
            'address' => $request->address,
            'locality' => $request->locality,
            'delivery_date' => Carbon::parse($request->delivery_date)->format('d/m/Y H:i'),
            'surtidor_name' => $request->surtidor_name,
            'observations' => $request->observations,
            'vendedor_name' => $user->name,
            'logo_url' => $logoUrl,
        ];

        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $pdfView = view('friends-and-family.sales.pdf', $pdfData);
        $dompdf->loadHtml($pdfView->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['SKU', 'Descripcion', 'Cantidad', 'Precio Unitario', 'Total']);
        foreach ($pdfItems as $row) {
            fputcsv($stream, [$row['sku'], $row['description'], $row['quantity'], $row['unit_price'], $row['total_price']]);
        }
        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        if ($request->filled('email_recipients')) {
            $recipients = array_map('trim', explode(';', $request->email_recipients));
            $recipients = array_filter($recipients, fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL));
            
            if (!empty($recipients)) {
                try {
                    $mailType = $isEditMode ? 'update' : 'new';
                    
                    $allDocs = FfOrderDocument::where('folio', $ventaFolio)->get()->map(function($doc) {
                        return [
                            'path' => $doc->path,
                            'name' => $doc->filename
                        ];
                    })->toArray();

                    Mail::to($recipients)->send(new OrderActionMail(
                        $pdfData, 
                        $mailType, 
                        $pdfContent, 
                        $csvContent,
                        $allDocs
                    ));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error enviando correo F&F: " . $e->getMessage());
                }
            }
        }
        
        return response($pdfContent, 200, ['Content-Type' => 'application/pdf', 'X-Venta-Folio' => $ventaFolio]);
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
        
        $pdfData['logo_url'] = Storage::disk('s3')->url('logoConsorcioMonter.png');
        
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

    public function downloadTemplate(Request $request)
    {
        $query = ffProduct::where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
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
            'Content-Disposition' => 'attachment; filename="plantilla_pedido_'.date('Y-m-d').'.csv"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, ['SKU', 'CANTIDAD', 'DESCRIPCION (Solo referencia)', 'STOCK DISPONIBLE']);

            foreach ($products as $product) {
                $reserved = $product->cartItems()->where('user_id', '!=', Auth::id())->sum('quantity');
                $totalStock = $product->movements()->sum('quantity');
                $available = max(0, $totalStock - $reserved);

                if ($available > 0) {
                    fputcsv($file, [
                        $product->sku,
                        '',
                        $product->description,
                        $available
                    ]);
                }
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function importOrder(Request $request)
    {
        $request->validate([
            'order_csv' => 'required|file|mimes:csv,txt'
        ]);

        $userId = Auth::id();
        $path = $request->file('order_csv')->getRealPath();
        $handle = fopen($path, 'r');
        
        fgetcsv($handle); 

        ffCartItem::where('user_id', $userId)->delete();

        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== FALSE) {
                $sku = trim($row[0] ?? '');
                $qty = intval(trim($row[1] ?? 0));

                if ($qty <= 0 || empty($sku)) continue;

                $product = ffProduct::where('sku', $sku)->first();

                if ($product) {
                    $reservedOthers = $product->cartItems()->where('user_id', '!=', $userId)->sum('quantity');
                    $totalStock = $product->movements()->sum('quantity');
                    $available = $totalStock - $reservedOthers;

                    if ($qty > $available) {
                        $qty = $available;
                        $errors[] = "SKU $sku: Ajustado a $available (Stock máx).";
                    }

                    if ($qty > 0) {
                        ffCartItem::create([
                            'user_id' => $userId,
                            'ff_product_id' => $product->id,
                            'quantity' => $qty
                        ]);
                        $importedCount++;
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar archivo: ' . $e->getMessage()], 500);
        } finally {
            fclose($handle);
        }

        $newCartItems = ffCartItem::where('user_id', $userId)
            ->get()
            ->map(fn($item) => ['id' => $item->ff_product_id, 'qty' => $item->quantity]);

        $msg = "Se importaron $importedCount productos.";
        if (count($errors) > 0) {
            $msg .= " Nota: Algunos items se ajustaron por falta de stock.";
        }

        return response()->json([
            'message' => $msg,
            'cart_items' => $newCartItems
        ]);
    }

}