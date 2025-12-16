<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffCartItem;
use App\Models\ffInventoryMovement;
use App\Models\ffProduct;
use App\Models\FfClient;
use App\Models\FfSalesChannel;
use App\Models\FfTransportLine;
use App\Models\FfPaymentCondition;
use App\Models\FfOrderDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderActionMail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\UploadedFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

class FfSalesController extends Controller
{
    private function getNextFolio(): int
    {
        $lastMovement = ffInventoryMovement::orderByDesc('folio')->first();
        return ($lastMovement ? $lastMovement->folio : 10000) + 1;
    }

    private function getCompanyInfo($areaId = null)
    {
        if (!$areaId && Auth::check()) {
            $areaId = Auth::user()->area_id;
        }

        return [
            'emitter_name' => 'Consorcio Monter S.A. de C.V.',
            'emitter_phone' => '5533347203',
            'emitter_address' => 'Jose de Teresa 65 A',
            'emitter_colonia' => 'San Angel, Alvaro Obregon, CDMX, Mexico',
            'emitter_cp' => '01000'
        ];
    }

    private function getLogoUrl($area)
    {
        if ($area && $area->icon_path) {
            return Storage::disk('s3')->url($area->icon_path);
        }
        return Storage::disk('s3')->url('logoConsorcioMonter.png');
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $editFolio = $request->input('edit_folio');

        $products = ffProduct::where('is_active', true)
            ->with(['channels', 'cartItems' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->withSum('movements', 'quantity')
            ->withSum(['cartItems as reserved_by_others' => function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            }], 'quantity')
            ->orderBy('description')
            ->get();

        $clients = FfClient::where('is_active', true)->with('branches')->orderBy('name')->get();
        $channels = FfSalesChannel::where('is_active', true)->orderBy('name')->get();
        $transports = FfTransportLine::where('is_active', true)->orderBy('name')->get();
        $payments = FfPaymentCondition::where('is_active', true)->orderBy('name')->get();

        $nextFolio = $this->getNextFolio();            
        
        return view('friends-and-family.sales.index', compact('products', 'nextFolio', 'clients', 'channels', 'transports', 'payments', 'editFolio'));
    }

    public function updateCartItem(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('ff_products', 'id')->where(function ($query) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where('area_id', Auth::user()->area_id);
                    }
                })
            ],
            'quantity' => 'required|integer|min:0',
            'folio' => 'nullable|integer'
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;
        $userId = Auth::id();

        if ($quantity <= 0) {
            ffCartItem::where('user_id', $userId)->where('ff_product_id', $productId)->delete();
            return response()->json(['message' => 'Producto liberado']);
        }

        ffCartItem::updateOrCreate(
            ['user_id' => $userId, 'ff_product_id' => $productId],
            ['quantity' => $quantity]
        );

        return response()->json(['message' => 'Producto agregado al pedido']);
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
        $discountsData = [];

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
                
                $discountsData[$productId] = $productMovements->first()->discount_percentage;
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

        $evidences = [];
        for($i=1; $i<=3; $i++) {
            if($url = $header->getEvidenceUrl($i)) {
                $evidences[] = ['index' => $i, 'url' => $url];
            }
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
                'order_type' => $header->order_type,
                'is_loan_returned' => $header->is_loan_returned,
                'ff_client_id' => $header->ff_client_id,
                'ff_client_branch_id' => $header->ff_client_branch_id,
                'ff_sales_channel_id' => $header->ff_sales_channel_id,
                'ff_transport_line_id' => $header->ff_transport_line_id,
                'ff_payment_condition_id' => $header->ff_payment_condition_id,
            ],
            'cart_items' => $cartItemsData,
            'discounts' => $discountsData,
            'evidences' => $evidences,
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
                    'area_id' => $mov->area_id, 
                    'quantity' => abs($mov->quantity),
                    'reason' => 'CANCELACIÓN Venta Folio ' . $folio . ': ' . $request->reason,
                    'client_name' => $mov->client_name,
                    'folio' => $folio,
                    'ff_client_id' => $mov->ff_client_id,
                    'ff_client_branch_id' => $mov->ff_client_branch_id,
                    'ff_sales_channel_id' => $mov->ff_sales_channel_id,
                    'ff_transport_line_id' => $mov->ff_transport_line_id,
                    'ff_payment_condition_id' => $mov->ff_payment_condition_id,
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
            'folio' => $isEditMode 
                ? 'required|integer|exists:ff_inventory_movements,folio'
                : 'required|integer|unique:ff_inventory_movements,folio',
            'client_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:50',
            'address' => 'required|string',
            'locality' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'order_type' => 'required|in:normal,remision,prestamo',
            'surtidor_name' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'email_recipients' => 'nullable|string',
            'discounts' => 'nullable|array',
            'documents' => 'array|max:5',
            'documents.*' => 'file|mimes:pdf|max:10240',
            'evidence_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'evidence_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'evidence_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'ff_client_id' => [
                'nullable', 
                Rule::exists('ff_clients', 'id')->where(function ($query) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where('area_id', Auth::user()->area_id);
                    }
                })
            ],
            'ff_client_branch_id' => 'nullable|exists:ff_client_branches,id',
            'ff_sales_channel_id' => [
                'nullable',
                Rule::exists('ff_sales_channels', 'id')->where(function ($query) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where('area_id', Auth::user()->area_id);
                    }
                })
            ],
            'ff_transport_line_id' => [
                'nullable',
                Rule::exists('ff_transport_lines', 'id')->where(function ($query) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where('area_id', Auth::user()->area_id);
                    }
                })
            ],
            'ff_payment_condition_id' => [
                'nullable',
                Rule::exists('ff_payment_conditions', 'id')->where(function ($query) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where('area_id', Auth::user()->area_id);
                    }
                })
            ],
        ]);

        $cartItems = ffCartItem::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 400);
        }

        $ventaFolio = $request->folio;
        $orderType = $request->order_type;
        $inputDiscounts = $request->input('discounts', []);
        
        $pdfItems = [];
        $grandTotal = 0;
        $orderHasBackorder = false;

        DB::beginTransaction();

        try {
            $existingEvidences = ['path_1' => null, 'path_2' => null, 'path_3' => null];

            if ($isEditMode) {
                $originalMovements = ffInventoryMovement::where('folio', $ventaFolio)
                    ->where('quantity', '<', 0)
                    ->get();
                
                if ($originalMovements->isNotEmpty()) {
                    $first = $originalMovements->first();
                    $existingEvidences['path_1'] = $first->evidence_path_1;
                    $existingEvidences['path_2'] = $first->evidence_path_2;
                    $existingEvidences['path_3'] = $first->evidence_path_3;
                }

                foreach($originalMovements as $mov) {
                    ffInventoryMovement::create([
                        'ff_product_id' => $mov->ff_product_id,
                        'user_id' => $user->id,
                        'area_id' => $mov->area_id, 
                        'quantity' => abs($mov->quantity),
                        'reason' => 'Ajuste por Edición Folio ' . $ventaFolio,
                        'folio' => $ventaFolio,
                        'ff_client_id' => $mov->ff_client_id,
                        'ff_client_branch_id' => $mov->ff_client_branch_id,
                        'ff_sales_channel_id' => $mov->ff_sales_channel_id,
                        'ff_transport_line_id' => $mov->ff_transport_line_id,
                        'ff_payment_condition_id' => $mov->ff_payment_condition_id,
                        'status' => 'approved',
                        'is_backorder' => false,
                        'backorder_fulfilled' => true
                    ]);
                }
            }

            $finalEvidencePaths = [];
            for ($i = 1; $i <= 3; $i++) {
                if ($request->hasFile("evidence_{$i}")) {
                    $finalEvidencePaths[$i] = $request->file("evidence_{$i}")->store("ff_evidence/{$ventaFolio}", 's3');
                } else {
                    $finalEvidencePaths[$i] = $existingEvidences["path_{$i}"];
                }
            }

            foreach ($cartItems as $item) {
                $product = $item->product;
                $quantity = $item->quantity;
                
                $basePrice = $product->unit_price ?? 0;
                $discountPercent = 0;
                $discountAmount = 0;
                $finalPrice = 0;

                if ($orderType === 'normal') {
                    $discountPercent = isset($inputDiscounts[$product->id]) ? floatval($inputDiscounts[$product->id]) : 0;
                    $discountAmount = $basePrice * ($discountPercent / 100);
                    $finalPrice = $basePrice - $discountAmount;
                } else {
                    $finalPrice = 0;
                    $discountPercent = 0;
                    $discountAmount = 0;
                }

                $totalLine = $quantity * $finalPrice;
                
                $currentStock = $product->movements()->sum('quantity');
                $isBackorder = ($currentStock - $quantity) < 0;
                
                if ($isBackorder) {
                    $orderHasBackorder = true;
                }

                ffInventoryMovement::create([
                    'ff_product_id' => $product->id,
                    'user_id' => $user->id,
                    'area_id' => $user->area_id, 
                    'quantity' => -$quantity,
                    'reason' => ucfirst($orderType) . ' F&F Folio ' . $ventaFolio,
                    'client_name' => $request->client_name,
                    'company_name' => $request->company_name,
                    'client_phone' => $request->client_phone,
                    'address' => $request->address,
                    'locality' => $request->locality,
                    'delivery_date' => $request->delivery_date,
                    'surtidor_name' => $request->surtidor_name,
                    'observations' => $request->observations,
                    'folio' => $ventaFolio,
                    'order_type' => $orderType,
                    'status' => 'pending',
                    'discount_percentage' => $discountPercent,
                    'evidence_path_1' => $finalEvidencePaths[1],
                    'evidence_path_2' => $finalEvidencePaths[2],
                    'evidence_path_3' => $finalEvidencePaths[3],
                    'notification_emails' => $request->email_recipients,
                    'is_backorder' => $isBackorder,
                    'backorder_fulfilled' => !$isBackorder,
                    'ff_client_id' => $request->ff_client_id,
                    'ff_client_branch_id' => $request->ff_client_branch_id,
                    'ff_sales_channel_id' => $request->ff_sales_channel_id,
                    'ff_transport_line_id' => $request->ff_transport_line_id,
                    'ff_payment_condition_id' => $request->ff_payment_condition_id,
                ]);

                $pdfItems[] = [
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'quantity' => $quantity,
                    'base_price' => $basePrice,
                    'discount_percentage' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'unit_price' => $finalPrice,
                    'total_price' => $totalLine,
                    'is_backorder' => $isBackorder,
                ];

                $grandTotal += $totalLine;
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
        
        $logoUrl = $this->getLogoUrl($user->area);
        $companyInfo = $this->getCompanyInfo($user->area_id);

        $pdfData = array_merge([
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
            'order_type' => $orderType,
        ], $companyInfo);

        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $pdfView = view('friends-and-family.sales.pdf', $pdfData);
        $dompdf->loadHtml($pdfView->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();

        try {
            $admins = \App\Models\User::where('is_area_admin', true)
                ->whereHas('area', function($q) {
                    $q->whereIn('name', ['Administración', 'Consorcio Monter']);
                })->get(); 
            
            if ($admins->isNotEmpty()) {
                $adminMailData = array_merge([
                    'folio' => $ventaFolio,
                    'client_name' => $request->client_name,
                    'company_name' => $request->company_name,
                    'delivery_date' => Carbon::parse($request->delivery_date)->format('d/m/Y H:i'),
                    'surtidor_name' => $request->surtidor_name,
                    'order_type' => $orderType,
                    'user_name' => $user->name,
                    'grandTotal' => $grandTotal,
                    'items' => $pdfItems,
                    'logo_url' => $logoUrl,
                    'has_backorder' => $orderHasBackorder,
                ], $companyInfo);
                
                foreach($admins as $admin) {
                    Mail::to($admin->email)->send(new OrderActionMail(
                        $adminMailData, 
                        'admin_alert', 
                        null,
                        null,
                        [],
                        null,
                        $admin->id
                    ));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error enviando alerta admin F&F: " . $e->getMessage());
        }
        
        return response($pdfContent, 200, ['Content-Type' => 'application/pdf', 'X-Venta-Folio' => $ventaFolio]);
    }

    public function printList(Request $request)
    {
        $data = $request->validate([
            'products' => 'required|array',
            'numSets'  => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $logoUrl = $this->getLogoUrl($user->area);

        $pdfData = [
            'products' => $data['products'],
            'numSets'  => $data['numSets'],
            'date'     => now()->format('d/m/Y'),
            'logo_url' => $logoUrl,
        ];
        
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

    private function getPrepFields()
    {
        return [
            'Revisión de UPC vs Factura' => 'revision_upc',
            'Distribución por Tienda' => 'distribucion_tienda',
            'Re-etiquetado' => 're_etiquetado',
            'Colocación de Sensor' => 'colocacion_sensor',
            'Preparado Especial' => 'preparado_especial',
            'Tipo de Unidad Aceptada' => 'tipo_unidad_aceptada',
            'Equipo de Seguridad' => 'equipo_seguridad',
            'Registro Patronal (SUA)' => 'registro_patronal',
            'Entrega con Otros Pedidos' => 'entrega_otros_pedidos',
            'Insumos y Herramientas' => 'insumos_herramientas',
            'Maniobra' => 'maniobra',
            'Identificaciones para Acceso' => 'identificaciones',
            'Etiqueta de Frágil' => 'etiqueta_fragil',
            'Tarima CHEP' => 'tarima_chep',
            'Granel' => 'granel',
            'Tarima Estándar' => 'tarima_estandar',
        ];
    }

    private function getDocFields()
    {
        return [
            'Factura' => 'doc_factura',
            'DO' => 'doc_do',
            'Carta Maniobra' => 'doc_carta_maniobra',
            'Carta Poder' => 'doc_carta_poder',
            'Orden de Compra' => 'doc_orden_compra',
            'Carta Confianza' => 'doc_carta_confianza',
            'Confirmación de Cita' => 'doc_confirmacion_cita',
            'Carta Caja Cerrada' => 'doc_carta_caja_cerrada',
            'Confirmación de Facturas' => 'doc_confirmacion_facturas',
            'Carátula de Entrega' => 'doc_caratula_entrega',
            'Pase Vehicular' => 'doc_pase_vehicular',
        ];
    }

    private function getEvidFields()
    {
        return [
            'Folio de Recibo' => 'evid_folio_recibo',
            'Factura Sellada o Firmada' => 'evid_factura_sellada',
            'Sello Tarima CHEP' => 'evid_sello_tarima',
            'Etiqueta de Recibo' => 'evid_etiqueta_recibo',
            'Acuse de Orden de Compra' => 'evid_acuse_oc',
            'Hoja de Rechazo' => 'evid_hoja_rechazo',
            'Anotación de Rechazo' => 'evid_anotacion_rechazo',
            'Contrarrecibo de Equipo' => 'evid_contrarrecibo',
            'Formato de Reparto' => 'evid_formato_reparto',
        ];
    }

    public function returnLoan(Request $request)
    {
        $request->validate(['folio' => 'required|integer']);
        $folio = $request->folio;
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $loanMovements = ffInventoryMovement::where('folio', $folio)
                ->where('order_type', 'prestamo')
                ->where('is_loan_returned', false)
                ->where('quantity', '<', 0)
                ->get();

            if ($loanMovements->isEmpty()) {
                throw new \Exception("Este folio no es un préstamo activo o ya fue devuelto.");
            }

            foreach($loanMovements as $mov) {
                ffInventoryMovement::create([
                    'ff_product_id' => $mov->ff_product_id,
                    'user_id' => $user->id,
                    'area_id' => $mov->area_id, 
                    'quantity' => abs($mov->quantity),
                    'reason' => 'DEVOLUCIÓN Préstamo Folio ' . $folio,
                    'folio' => $folio,
                    'order_type' => 'prestamo',
                    'is_loan_returned' => true,
                    'loan_returned_at' => now(),
                    'client_name' => $mov->client_name, 
                ]);

                ffInventoryMovement::where('id', $mov->id)->update([
                    'is_loan_returned' => true,
                    'loan_returned_at' => now()
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Préstamo marcado como devuelto e inventario restaurado.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function clearCart()
    {
        try {
            $userId = Auth::id();
            ffCartItem::where('user_id', $userId)->delete();
            return response()->json(['message' => 'Carrito liberado']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error'], 500);
        }
    }    
}