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
use Illuminate\Support\Facades\Log;
use App\Models\Area;
use App\Models\FfWarehouse;
use App\Models\FfQuality;

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

        $area = $areaId ? Area::find($areaId) : null;

        if ($area) {
            return [
                'emitter_name'    => $area->emitter_name ?: 'Por definir',
                'emitter_phone'   => $area->emitter_phone ?: 'Por definir',
                'emitter_address' => $area->emitter_address ?: 'Por definir',
                'emitter_colonia' => $area->emitter_colonia ?: 'Por definir',
                'emitter_cp'      => $area->emitter_cp ?: 'Por definir'
            ];
        }

        return [
            'emitter_name'    => 'Por definir',
            'emitter_phone'   => 'Por definir',
            'emitter_address' => 'Por definir',
            'emitter_colonia' => 'Por definir',
            'emitter_cp'      => 'Por definir'
        ];
    }

    private function getLogoUrl($area)
    {
        if ($area && $area->icon_path) {
            return Storage::disk('s3')->url($area->icon_path);
        }
        return Storage::disk('s3')->url('LogoAzulm.PNG');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->hasFfPermission('sales.view')) {
            abort(403, 'No tienes permiso para ver el módulo de ventas.');
        }

        $userId = $user->id;
        $editFolio = $request->input('edit_folio');
        $productsQuery = ffProduct::where('is_active', true);
        
        if (!$user->isSuperAdmin()) {
            $productsQuery->where('area_id', $user->area_id);
        }

        elseif ($user->isSuperAdmin() && $request->filled('area_id')) {
            $productsQuery->where('area_id', $request->input('area_id'));
        }        

        $areas = [];
        if ($user->isSuperAdmin()) {
            $areas = Area::all();
        }        

        $products = $productsQuery
            ->with(['channels', 'cartItems' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderBy('description')
            ->get();

        $inventoryBreakdown = ffInventoryMovement::selectRaw('ff_product_id, ff_warehouse_id, ff_quality_id, SUM(quantity) as total_stock')
            ->whereIn('ff_product_id', $products->pluck('id'))
            ->groupBy('ff_product_id', 'ff_warehouse_id', 'ff_quality_id')
            ->get();

        $reservationsBreakdown = ffCartItem::selectRaw('ff_product_id, ff_warehouse_id, ff_quality_id, SUM(quantity) as reserved_qty')
            ->where('user_id', '!=', $userId)
            ->whereIn('ff_product_id', $products->pluck('id'))
            ->whereNotNull('ff_warehouse_id')
            ->groupBy('ff_product_id', 'ff_warehouse_id', 'ff_quality_id')
            ->get();

        $products->each(function($product) use ($inventoryBreakdown, $reservationsBreakdown) {
            
            $stockMap = [];

            $pMoves = $inventoryBreakdown->where('ff_product_id', $product->id);
            $pRes = $reservationsBreakdown->where('ff_product_id', $product->id);

            foreach($pMoves as $move) {
                $key = $move->ff_warehouse_id . '_' . ($move->ff_quality_id ?? 'std');
                $stockMap[$key] = (int)$move->total_stock;
            }

            foreach($pRes as $res) {
                $key = $res->ff_warehouse_id . '_' . ($res->ff_quality_id ?? 'std');
                if(isset($stockMap[$key])) {
                    $stockMap[$key] -= (int)$res->reserved_qty;
                } else {
                    $stockMap[$key] = -((int)$res->reserved_qty);
                }
            }

            array_walk($stockMap, function(&$val) { $val = max(0, $val); });

            $product->setAttribute('stock_map', $stockMap);
            $product->setAttribute('reserved_by_others', 0);
        });

        $clientsQuery = FfClient::where('is_active', true)->with('branches');
        $channelsQuery = FfSalesChannel::where('is_active', true);
        $transportsQuery = FfTransportLine::where('is_active', true);
        $paymentsQuery = FfPaymentCondition::where('is_active', true);
        $warehousesQuery = FfWarehouse::where('is_active', true);
        $qualitiesQuery = FfQuality::where('is_active', true);
        
        if (!$user->isSuperAdmin()) {
            $clientsQuery->where('area_id', $user->area_id);
            $channelsQuery->where('area_id', $user->area_id);
            $transportsQuery->where('area_id', $user->area_id);
            $paymentsQuery->where('area_id', $user->area_id);
            $warehousesQuery->where(function($q) use ($user) {
                $q->where('area_id', $user->area_id)
                  ->orWhereNull('area_id');
            });
            $qualitiesQuery->where('area_id', $user->area_id);
        } elseif ($user->isSuperAdmin() && $request->filled('area_id')) {
            $areaId = $request->input('area_id');
            $clientsQuery->where('area_id', $areaId);
            $channelsQuery->where('area_id', $areaId);
            $transportsQuery->where('area_id', $areaId);
            $paymentsQuery->where('area_id', $areaId);
            $warehousesQuery->where('area_id', $areaId);
            $qualitiesQuery->where('area_id', $areaId);
        }

        $clients = $clientsQuery->orderBy('name')->get();
        $channels = $channelsQuery->orderBy('name')->get();
        $transports = $transportsQuery->orderBy('name')->get();
        $payments = $paymentsQuery->orderBy('name')->get();
        $warehouses = $warehousesQuery->orderBy('description')->get();        
        $qualities = $qualitiesQuery->orderBy('name')->get();

        $nextFolio = $this->getNextFolio();            
        
        return view('friends-and-family.sales.index', compact(
            'products', 'nextFolio', 'clients', 'channels', 
            'transports', 'payments', 'editFolio', 'warehouses', 'areas', 'qualities'
        ));
    }

    public function updateCartItem(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.checkout')) {
            return response()->json(['message' => 'No tienes permiso para realizar ventas.'], 403);
        }

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
            'warehouse_id' => [
                'required',
                Rule::exists('ff_warehouses', 'id')->where(function ($query) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where(function($q) {
                            $q->where('area_id', Auth::user()->area_id)
                              ->orWhereNull('area_id');
                        });
                    }
                })
            ],
            'ff_quality_id' => 'nullable|exists:ff_qualities,id'
        ]);

        $productId = $request->product_id;
        $warehouseId = $request->warehouse_id;
        $qualityId = $request->ff_quality_id;
        $quantity = $request->quantity;
        $userId = Auth::id();

        if ($quantity <= 0) {
            $query = ffCartItem::where('user_id', $userId)
                    ->where('ff_product_id', $productId)
                    ->where('ff_warehouse_id', $warehouseId);
            
            if ($qualityId) {
                $query->where('ff_quality_id', $qualityId);
            } else {
                $query->whereNull('ff_quality_id');
            }
            
            $query->delete();
            return response()->json(['message' => 'Producto liberado']);
        }

        ffCartItem::updateOrCreate(
            [
                'user_id' => $userId, 
                'ff_product_id' => $productId,
                'ff_quality_id' => $qualityId
            ],
            [
                'quantity' => $quantity,
                'ff_warehouse_id' => $warehouseId
            ]
        );

        return response()->json(['message' => 'Producto agregado al pedido']);
    }

    public function getReservations()
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.view')) {
            return response()->json(['message' => 'Sin permisos.'], 403);
        }

        $userId = Auth::id();
        
        $query = ffCartItem::where('user_id', '!=', $userId);
        
        if (!Auth::user()->isSuperAdmin()) {
            $query->whereHas('product', function($q) {
                $q->where('area_id', Auth::user()->area_id);
            });
        }

        $reservations = $query->groupBy('ff_product_id')
            ->select('ff_product_id', DB::raw('SUM(quantity) as reserved_quantity'))
            ->pluck('reserved_quantity', 'ff_product_id');
        
        return response()->json($reservations);
    }

    public function searchOrder(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.checkout')) {
            return response()->json(['message' => 'No tienes permiso para editar pedidos.'], 403);
        }

        $request->validate(['folio' => 'required|integer']);
        
        $movements = ffInventoryMovement::where('folio', $request->folio)
            ->with('product')
            ->orderBy('created_at', 'desc') 
            ->get();

        if ($movements->isEmpty()) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        }

        $header = $movements->first();
        if (in_array($header->status, ['cancelled', 'rejected'])) {
            return response()->json([
                'message' => 'No es posible editar un pedido que ya está CANCELADO o RECHAZADO.'
            ], 422);
        }        
        if (!Auth::user()->isSuperAdmin() && $header->area_id !== Auth::user()->area_id) {
            return response()->json(['message' => 'No tienes permiso para cargar este pedido.'], 403);
        }

        $user = Auth::user();

        ffCartItem::where('user_id', $user->id)->delete();

        $cartItemsData = [];
        $discountsData = [];

        foreach($movements as $mov) {
            $finalQty = abs($mov->quantity);
            
            if ($mov->quantity < 0) {
                ffCartItem::create([
                    'user_id' => $user->id,
                    'ff_product_id' => $mov->ff_product_id,
                    'ff_warehouse_id' => $mov->ff_warehouse_id,
                    'ff_quality_id' => $mov->ff_quality_id,
                    'quantity' => $finalQty
                ]);

                $cartItemsData[] = [
                    'product_id' => $mov->ff_product_id,
                    'quantity' => $finalQty,
                    'warehouse_id' => $mov->ff_warehouse_id,
                    'quality_id' => $mov->ff_quality_id
                ];
                
                $discountsData[$mov->ff_product_id] = $mov->discount_percentage;
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
            Log::error("Error cargando documentos: " . $e->getMessage());
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
                'ff_warehouse_id' => $header->ff_warehouse_id,
                'ff_quality_id' => $header->ff_quality_id,
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
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.cancel')) {
            return response()->json(['message' => 'No tienes permiso para cancelar pedidos.'], 403);
        }

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
                throw new \Exception("El pedido no contiene productos o no existe.");
            }

            $header = $originalMovements->first();

            if (in_array($header->status, ['cancelled', 'rejected'])) {
                throw new \Exception("Este pedido ya se encuentra cancelado o rechazado.");
            }

            if (!$user->isSuperAdmin() && $header->area_id !== $user->area_id) {
                throw new \Exception("No tienes permiso para cancelar este pedido.");
            }

            ffInventoryMovement::where('folio', $folio)->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);

            foreach($originalMovements as $mov) {
                ffInventoryMovement::create([
                    'ff_product_id' => $mov->ff_product_id,
                    'user_id' => $user->id,
                    'area_id' => $mov->area_id, 
                    'quantity' => abs($mov->quantity),
                    'reason' => 'CANCELACIÓN Venta Folio ' . $folio . ': ' . $request->reason,
                    'client_name' => $mov->client_name,
                    'folio' => $folio,
                    'status' => 'cancelled',
                    'ff_warehouse_id' => $mov->ff_warehouse_id, 
                    'ff_client_id' => $mov->ff_client_id,
                    'ff_client_branch_id' => $mov->ff_client_branch_id,
                    'ff_sales_channel_id' => $mov->ff_sales_channel_id,
                    'ff_transport_line_id' => $mov->ff_transport_line_id,
                    'ff_payment_condition_id' => $mov->ff_payment_condition_id,
                    'ff_quality_id' => $mov->ff_quality_id,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Pedido cancelado y stock restaurado.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function checkout(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.checkout')) {
            return response()->json(['message' => 'No tienes permiso para realizar el checkout.'], 403);
        }

        $user = Auth::user();

        $targetAreaId = $user->area_id;
        if ($user->isSuperAdmin() && $request->filled('area_id')) {
            $targetAreaId = $request->input('area_id');
        }        
        
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
            'ff_warehouse_id' => [
                'required', 
                Rule::exists('ff_warehouses', 'id')->where(function ($query) {
                    if (!Auth::user()->isSuperAdmin()) {
                        $query->where(function($q) {
                            $q->where('area_id', Auth::user()->area_id)
                              ->orWhereNull('area_id');
                        });
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

        $cartItems = ffCartItem::where('user_id', $user->id)->with(['product', 'warehouse'])->get();

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
            if ($isEditMode) {
                $existingOrder = ffInventoryMovement::where('folio', $ventaFolio)->first();
                
                if ($existingOrder && !$user->isSuperAdmin() && $existingOrder->area_id !== $user->area_id) {
                    throw new \Exception("No tienes permiso para editar este folio.");
                }

                if ($existingOrder && in_array($existingOrder->status, ['cancelled', 'rejected'])) {
                    throw new \Exception("Acción denegada: Este pedido está cancelado y no puede ser modificado.");
                }
                
                ffInventoryMovement::where('folio', $ventaFolio)->delete();
            }

            foreach ($cartItems as $item) {
                $product = $item->product;
                
                if (!$user->isSuperAdmin() && $product->area_id !== $user->area_id) {
                    throw new \Exception("El producto SKU {$product->sku} no pertenece a tu área.");
                }

                $quantity = $item->quantity;
                $warehouseId = $item->ff_warehouse_id ?? $request->ff_warehouse_id;
                $qualityId = $item->ff_quality_id;
                
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
                
                $currentStockQuery = $product->movements()
                    ->where('ff_warehouse_id', $warehouseId);
                
                if ($qualityId) {
                    $currentStockQuery->where('ff_quality_id', $qualityId);
                } else {
                    $currentStockQuery->whereNull('ff_quality_id');
                }

                $currentStock = $currentStockQuery->sum('quantity');
                $isBackorder = ($currentStock - $quantity) < 0;
                
                if ($isBackorder) {
                    $orderHasBackorder = true;
                }

                ffInventoryMovement::create([
                    'ff_product_id' => $product->id,
                    'user_id' => $user->id,
                    'area_id' => $targetAreaId,
                    'quantity' => -$quantity,
                    'reason' => ucfirst($orderType) . ' Venta Folio ' . $ventaFolio,
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
                    'was_edited' => $isEditMode,
                    'discount_percentage' => $discountPercent,
                    'evidence_path_1' => null,
                    'evidence_path_2' => null,
                    'evidence_path_3' => null,
                    'notification_emails' => $request->email_recipients,
                    'is_backorder' => $isBackorder,
                    'backorder_fulfilled' => !$isBackorder,
                    'ff_client_id' => $request->ff_client_id,
                    'ff_client_branch_id' => $request->ff_client_branch_id,
                    'ff_sales_channel_id' => $request->ff_sales_channel_id,
                    'ff_warehouse_id' => $warehouseId, 
                    'ff_quality_id' => $qualityId,
                    'ff_transport_line_id' => $request->ff_transport_line_id,
                    'ff_payment_condition_id' => $request->ff_payment_condition_id,
                ]);

                $qualityName = 'Estándar';
                if ($qualityId) {
                    $q = FfQuality::find($qualityId);
                    if ($q) $qualityName = $q->name;
                }

                $pdfItems[] = [
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'quality' => $qualityName,
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

        $targetAreaModel = \App\Models\Area::find($targetAreaId);
        $logoUrl = $this->getLogoUrl($targetAreaModel);
        
        $logoBase64 = null;
        try {
            $logoKey = $targetAreaModel->icon_path ?? 'LogoAzulm.PNG'; 
            
            if (Storage::disk('s3')->exists($logoKey)) {
                $imageContent = Storage::disk('s3')->get($logoKey);
                $type = pathinfo($logoKey, PATHINFO_EXTENSION);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($imageContent);
            }
        } catch (\Exception $e) {
            $logoBase64 = $logoUrl; 
        }

        $companyInfo = $this->getCompanyInfo($targetAreaId);

        $warehouseName = 'N/A';
        if ($request->ff_warehouse_id) {
            $wh = \App\Models\FfWarehouse::find($request->ff_warehouse_id);
            if ($wh) $warehouseName = $wh->code . ' - ' . $wh->description;
        }

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
            'logo_url' => $logoBase64,
            'order_type' => $orderType,
            'warehouse_name' => $warehouseName,
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
                ->where('area_id', $targetAreaId)
                ->get(); 
            
            if ($admins->isNotEmpty()) {
                
                $emailType = 'admin_alert'; 

                $adminMailData = array_merge([
                    'folio' => $ventaFolio,
                    'client_name' => $request->client_name,
                    'company_name' => $request->company_name,
                    'delivery_date' => Carbon::parse($request->delivery_date)->format('d/m/Y H:i'),
                    'surtidor_name' => $request->surtidor_name,
                    'order_type' => $orderType,
                    'user_name' => $user->name,
                    'user_area' => $targetAreaModel->name ?? 'N/A',
                    'grandTotal' => $grandTotal,
                    'items' => $pdfItems,
                    'logo_url' => $logoUrl,
                    'has_backorder' => $orderHasBackorder,
                    'warehouse_name' => $warehouseName,
                    'is_edit' => $isEditMode
                ], $companyInfo);
                
                foreach($admins as $admin) {
                    Mail::to($admin->email)->send(new OrderActionMail(
                        $adminMailData, 
                        $emailType,
                        null, null, [], null, $admin->id
                    ));
                }
            } else {
                Log::warning("Pedido #{$ventaFolio} creado en área {$targetAreaId} sin administradores para notificar.");
            }

        } catch (\Exception $e) {
            Log::error("Error enviando alerta admin: " . $e->getMessage());
        }

        return response($pdfContent, 200, ['Content-Type' => 'application/pdf', 'X-Venta-Folio' => $ventaFolio]);
    }

    public function printList(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.view')) {
            abort(403, 'Sin permisos.');
        }

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
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.import')) {
            abort(403, 'Sin permisos de importación.');
        }

        $query = ffProduct::where('is_active', true);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

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
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.import')) {
            return response()->json(['message' => 'No tienes permiso para importar pedidos.'], 403);
        }

        $request->validate([
            'order_csv' => 'required|file|mimes:csv,txt'
        ]);

        $userId = Auth::id();
        $userAreaId = Auth::user()->area_id;
        $isSuperAdmin = Auth::user()->isSuperAdmin();

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

                $query = ffProduct::where('sku', $sku);
                if (!$isSuperAdmin) {
                    $query->where('area_id', $userAreaId);
                }
                $product = $query->first();

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

    public function getLoanDetails(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.loans')) {
            return response()->json(['message' => 'Sin permisos para gestionar préstamos.'], 403);
        }

        $request->validate(['folio' => 'required|integer']);

        $loanItems = ffInventoryMovement::where('folio', $request->folio)
            ->where('order_type', 'prestamo')
            ->where('quantity', '<', 0)
            ->with(['product', 'quality', 'warehouse'])
            ->get()
            ->map(function($item) {
                $originalQty = abs($item->quantity);
                $returnedSoFar = $item->returned_quantity ?? 0;
                $remaining = $originalQty - $returnedSoFar;

                return [
                    'movement_id' => $item->id,
                    'sku' => $item->product->sku,
                    'description' => $item->product->description,
                    'quality_name' => $item->quality->name ?? 'Estándar',
                    'original_qty' => $originalQty,
                    'returned_so_far' => $returnedSoFar,
                    'remaining_qty' => $remaining,
                    'return_now' => 0,
                    'warehouse_name' => $item->warehouse->code ?? 'N/A'
                ];
            });

        return response()->json(['items' => $loanItems]);
    }

    public function processLoanReturn(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('sales.loans')) {
            return response()->json(['message' => 'Sin permisos para gestionar devoluciones.'], 403);
        }

        $request->validate([
            'folio' => 'required|integer',
            'items' => 'required|array',
            'close_loan' => 'boolean'
        ]);

        $user = Auth::user();
        DB::beginTransaction();

        try {
            $allFullyReturned = true;

            foreach ($request->items as $row) {
                $qtyToReturn = intval($row['return_now']);
                
                if ($qtyToReturn <= 0) continue;

                $originalMov = ffInventoryMovement::lockForUpdate()->find($row['movement_id']);
                
                $maxReturnable = abs($originalMov->quantity) - $originalMov->returned_quantity;

                if ($qtyToReturn > $maxReturnable) {
                    throw new \Exception("Error: Intentas devolver $qtyToReturn del SKU {$originalMov->product->sku}, pero solo quedan $maxReturnable pendientes.");
                }

                ffInventoryMovement::create([
                    'ff_product_id' => $originalMov->ff_product_id,
                    'user_id' => $user->id,
                    'area_id' => $originalMov->area_id,
                    'quantity' => $qtyToReturn,
                    'reason' => 'ABONO a Préstamo Folio ' . $request->folio,
                    'folio' => $request->folio,
                    'order_type' => 'prestamo',
                    'client_name' => $originalMov->client_name,
                    'ff_warehouse_id' => $originalMov->ff_warehouse_id,
                    'ff_quality_id' => $originalMov->ff_quality_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $originalMov->returned_quantity += $qtyToReturn;
                $originalMov->save();

                if (abs($originalMov->quantity) > $originalMov->returned_quantity) {
                    $allFullyReturned = false; // Aún debe de este producto
                }
            }

            if ($request->close_loan || $allFullyReturned) {
                ffInventoryMovement::where('folio', $request->folio)
                    ->where('order_type', 'prestamo')
                    ->update(['is_loan_returned' => true, 'loan_returned_at' => now()]);
                
                $msg = $request->close_loan 
                    ? "Devolución registrada y Préstamo LIQUIDADO (Cierre forzoso)."
                    : "Devolución completa registrada. Préstamo cerrado.";
            } else {
                $msg = "Devolución parcial registrada. El préstamo sigue ABIERTO.";
            }

            DB::commit();
            return response()->json(['message' => $msg]);

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