<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffInventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\OrderActionMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Models\FfOrderDocument;
use App\Models\FfClient;
use App\Models\User;
use Dompdf\Dompdf;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\FriendsAndFamily\FfAdministrationController;
use App\Models\Area;
use App\Models\FfWarehouse;
use App\Models\FfQuality;

class FfOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ffInventoryMovement::query()
            ->where('quantity', '<', 0)
            ->whereIn('order_type', ['normal', 'remision', 'prestamo']); 

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }        

        if ($request->filled('warehouse_id')) {
            $query->where('ff_warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('quality_id')) {
            $query->where('ff_quality_id', $request->input('quality_id'));
        }

        if ($request->filled('client')) {
            $search = $request->input('client');
            $query->where(function($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');

            if ($status === 'loan_pending') {
                $query->where('order_type', 'prestamo')
                      ->where('status', 'approved')
                      ->where('is_loan_returned', false);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('type')) {
            $query->where('order_type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $query->select(
                'folio', 
                'client_name', 
                'company_name', 
                'order_type', 
                'status', 
                'delivery_date', 
                'created_at',
                'user_id',
                'area_id',
                'ff_warehouse_id',
                'is_loan_returned',
                DB::raw('SUM(ABS(quantity)) as total_items'),
                DB::raw('MAX(id) as id'),
                DB::raw('MAX(CASE WHEN status != "cancelled" AND is_backorder = 1 AND backorder_fulfilled = 0 THEN 1 ELSE 0 END) as has_active_backorder'),
                DB::raw('(SELECT COUNT(*) FROM ff_order_evidences WHERE ff_order_evidences.folio = ff_inventory_movements.folio) as evidences_count')
            )
            ->groupBy(
                'folio', 
                'client_name', 
                'company_name', 
                'order_type', 
                'status', 
                'delivery_date', 
                'created_at', 
                'user_id',
                'area_id',
                'ff_warehouse_id',
                'is_loan_returned'
            );

        if ($request->boolean('show_backorders')) {
            $query->having('has_active_backorder', 1);
        }

        $orders = $query->with('warehouse')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
            
        $warehousesQuery = FfWarehouse::where('is_active', true);
        $qualitiesQuery = FfQuality::where('is_active', true)->orderBy('name');

        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
            $qualitiesQuery->where('area_id', Auth::user()->area_id);
        } elseif (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $warehousesQuery->where('area_id', $request->input('area_id'));
            $qualitiesQuery->where('area_id', $request->input('area_id'));
        }

        $warehouses = $warehousesQuery->orderBy('description')->get();
        $qualities = $qualitiesQuery->get();

        return view('friends-and-family.orders.index', compact('orders', 'warehouses', 'qualities'));
    }

    public function show($folio)
    {
        $movements = ffInventoryMovement::where('folio', $folio)
            ->where('quantity', '<', 0)
            ->with(['product', 'user', 'approver', 'warehouse', 'quality'])
            ->get();

        if ($movements->isEmpty()) {
            return redirect()->route('ff.orders.index')->with('error', 'Pedido no encontrado.');
        }

        $header = $movements->first();

        if (!Auth::user()->isSuperAdmin() && $header->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para ver este pedido.');
        }

        $totalItems = $movements->sum(fn($m) => abs($m->quantity));

        $totalValue = $movements->sum(function($m) {
            if ($m->order_type === 'normal') {
                $basePrice = $m->product->unit_price;
                $discount = $m->discount_percentage ?? 0;
                $finalPrice = $basePrice - ($basePrice * ($discount / 100));
                return abs($m->quantity) * $finalPrice;
            }
            return 0;
        });

        $documents = [];
        try {
            if (class_exists(\App\Models\FfOrderDocument::class)) {
                $documents = \App\Models\FfOrderDocument::where('folio', $folio)->get();
            }
        } catch (\Exception $e) { }

        return view('friends-and-family.orders.show', compact('movements', 'header', 'documents', 'totalItems', 'totalValue'));
    }

    private function getCompanyInfo($areaId = null)
    {
        if (!$areaId && Auth::check()) {
            $areaId = Auth::user()->area_id;
        }

        $area = $areaId ? Area::find($areaId) : null;

        if ($area) {
            return [
                'emitter_name'    => $area->emitter_name ?? 'Pendiente de definir',
                'emitter_phone'   => $area->emitter_phone ?? 'Pendiente de definir',
                'emitter_address' => $area->emitter_address ?? 'Pendiente de definir',
                'emitter_colonia' => $area->emitter_colonia ?? 'Pendiente de definir',
                'emitter_cp'      => $area->emitter_cp ?? 'Pendiente de definir'
            ];
        }

        return [
            'emitter_name'    => 'Pendiente de definir',
            'emitter_phone'   => 'Pendiente de definir',
            'emitter_address' => 'Pendiente de definir',
            'emitter_colonia' => 'Pendiente de definir',
            'emitter_cp'      => 'Pendiente de definir'
        ];
    }

    private function getLogoUrl($areaId)
    {
        if ($areaId) {
            $area = Area::find($areaId);
            if ($area && $area->icon_path) {
                return Storage::disk('s3')->url($area->icon_path);
            }
        }
        return Storage::disk('s3')->url('LogoAzulm.PNG');
    }  

    public function approve($folio, \App\Services\Sync\FnFToWmsService $syncService)
    {
        if (method_exists($this, 'authorizeAdmin')) {
            $this->authorizeAdmin();
        }

        $check = ffInventoryMovement::where('folio', $folio)->firstOrFail();
        
        if (!Auth::user()->isSuperAdmin() && $check->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para aprobar este pedido.');
        }

        ffInventoryMovement::where('folio', $folio)->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        
        // Trigger WMS Sync
        $syncService->syncOutboundOrderFromFolio($folio);

        $movements = ffInventoryMovement::where('folio', $folio)
            ->where('quantity', '<', 0)
            ->with(['product', 'user', 'quality'])
            ->get();
            
        // ... (rest of the method)

        if ($movements->isNotEmpty()) {
            $header = $movements->first();
            
            $emailType = $header->was_edited ? 'update' : 'new';

            $companyInfo = $this->getCompanyInfo($header->area_id);
            $logoUrl = $this->getLogoUrl($header->area_id);

            $warehouseName = 'N/A';
            if ($header->ff_warehouse_id) {
                $wh = \App\Models\FfWarehouse::find($header->ff_warehouse_id);
                if ($wh) $warehouseName = $wh->code . ' - ' . $wh->description;
            }

            if (!empty($header->notification_emails)) {
                try {
                    $pdfItems = [];
                    $grandTotal = 0;
                    
                    foreach($movements as $m) {
                        $basePrice = $m->product->unit_price;
                        $discountPercent = $m->discount_percentage ?? 0;
                        
                        if ($m->order_type === 'normal') {
                            $discountAmount = $basePrice * ($discountPercent / 100);
                            $finalPrice = $basePrice - $discountAmount;
                        } else {
                            $basePrice = 0;
                            $discountPercent = 0;
                            $discountAmount = 0;
                            $finalPrice = 0;
                        }

                        $totalLine = abs($m->quantity) * $finalPrice;
                        $grandTotal += $totalLine;

                        $pdfItems[] = [
                            'sku' => $m->product->sku,
                            'description' => $m->product->description,
                            'quality' => $m->quality ? $m->quality->name : 'Estándar',
                            'quantity' => abs($m->quantity),
                            'base_price' => $basePrice,
                            'discount_percentage' => $discountPercent,
                            'discount_amount' => $discountAmount,
                            'unit_price' => $finalPrice,
                            'total_price' => $totalLine,
                            'is_backorder' => $m->is_backorder
                        ];
                    }

                    $pdfData = array_merge([
                        'items' => $pdfItems,
                        'grandTotal' => $grandTotal,
                        'folio' => $folio,
                        'date' => $header->created_at->format('d/m/Y'),
                        'client_name' => $header->client_name,
                        'company_name' => $header->company_name,
                        'client_phone' => $header->client_phone,
                        'address' => $header->address,
                        'locality' => $header->locality,
                        'delivery_date' => $header->delivery_date ? $header->delivery_date->format('d/m/Y H:i') : '',
                        'surtidor_name' => $header->surtidor_name,
                        'observations' => $header->observations,
                        'vendedor_name' => $header->user->name ?? 'N/A',
                        'logo_url' => $logoUrl,
                        'order_type' => $header->order_type,
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

                    $stream = fopen('php://temp', 'r+');
                    fputcsv($stream, ['SKU', 'Descripcion', 'Calidad', 'Cantidad', 'Precio Unitario', 'Total']);
                    foreach ($pdfItems as $row) {
                        fputcsv($stream, [$row['sku'], $row['description'], $row['quality'], $row['quantity'], $row['unit_price'], $row['total_price']]);
                    }
                    rewind($stream);
                    $csvContent = stream_get_contents($stream);
                    fclose($stream);

                    $conditionsPdfContent = null;
                    if ($header->ff_client_id) {
                        $client = FfClient::with('deliveryConditions')->find($header->ff_client_id);
                        
                        if ($client && $client->deliveryConditions && method_exists(FfAdministrationController::class, 'getPrepFieldsStatic')) {
                            $condData = [
                                'client' => $client,
                                'conditions' => $client->deliveryConditions,
                                'logoUrl' => $logoUrl,
                                'specific_address' => $header->address . ', ' . $header->locality,
                                'specific_observations' => $header->observations,
                                'prepFields' => FfAdministrationController::getPrepFieldsStatic(), 
                                'docFields' => FfAdministrationController::getDocFieldsStatic(),
                                'evidFields' => FfAdministrationController::getEvidFieldsStatic(),
                            ];
                            $pdfCond = Pdf::loadView('friends-and-family.admin.conditions-pdf', $condData);
                            $pdfCond->setPaper('A4', 'portrait');
                            $pdfCond->setOption('isRemoteEnabled', true);
                            $conditionsPdfContent = $pdfCond->output();
                        }
                    }

                    $allDocs = FfOrderDocument::where('folio', $folio)->get()->map(function($doc) {
                        return ['path' => $doc->path, 'name' => $doc->filename];
                    })->toArray();

                    $recipients = array_filter(array_map('trim', explode(',', str_replace(';', ',', $header->notification_emails))));
                    
                    if (!empty($recipients)) {
                        Mail::to($recipients)->send(new OrderActionMail(
                            $pdfData, 
                            $emailType,
                            $pdfContent, 
                            $csvContent,
                            $allDocs,
                            $conditionsPdfContent
                        ));
                    }

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error enviando correo de aprobación (#$folio): " . $e->getMessage());
                }
            }
        }

        return redirect()->back()->with('success', 'Pedido #' . $folio . ' APROBADO y notificaciones enviadas.');
    }

    public function reject(Request $request, $folio)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $movements = ffInventoryMovement::where('folio', $folio)->where('quantity', '<', 0)->get();

        if ($movements->isEmpty()) {
            return redirect()->back()->with('error', 'Pedido no encontrado.');
        }

        $header = $movements->first();
        if (!Auth::user()->isSuperAdmin() && $header->area_id !== Auth::user()->area_id) {
            abort(403);
        }

        if ($header->status !== 'pending') {
            return redirect()->back()->with('error', 'Solo se pueden rechazar pedidos pendientes.');
        }

        DB::beginTransaction();

        try {
            ffInventoryMovement::where('folio', $folio)->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            foreach ($movements as $mov) {
                ffInventoryMovement::create([
                    'ff_product_id' => $mov->ff_product_id,
                    'user_id' => Auth::id(),
                    'area_id' => $mov->area_id,
                    'quantity' => abs($mov->quantity),
                    'reason' => 'RECHAZO Pedido #' . $folio . ': ' . $request->reason,
                    'folio' => $folio,
                    'status' => 'rejected',
                    'client_name' => $mov->client_name,
                    'ff_client_id' => $mov->ff_client_id,
                    'ff_warehouse_id' => $mov->ff_warehouse_id,
                    'ff_quality_id' => $mov->ff_quality_id,
                ]);
            }

            DB::commit();

            if ($header->user && $header->user->email) {
                $companyInfo = $this->getCompanyInfo($header->area_id);
                $logoUrl = $this->getLogoUrl($header->area_id);
                
                $mailData = array_merge([
                    'folio' => $folio,
                    'client_name' => $header->client_name,
                    'company_name' => $header->company_name,
                    'delivery_date' => $header->delivery_date ? $header->delivery_date->format('d/m/Y H:i') : 'N/A',
                    'surtidor_name' => $header->surtidor_name,
                    'cancel_reason' => $request->reason,
                    'items' => [],
                    'logo_url' => $logoUrl
                ], $companyInfo);

                try {
                    Mail::to($header->user->email)->send(new OrderActionMail($mailData, 'cancel'));
                } catch (\Exception $e) {}
            }

            return redirect()->back()->with('success', 'Pedido rechazado y stock liberado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al rechazar: ' . $e->getMessage());
        }
    }

    private function authorizeAdmin()
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->is_area_admin) {
            abort(403, 'No tienes permisos para autorizar pedidos.');
        }
    }

    public function emailApprove($folio, $adminId, \App\Services\Sync\FnFToWmsService $syncService)
    {
        if (!request()->hasValidSignature()) {
            return view('friends-and-family.orders.email-response', ['status' => 'error', 'message' => 'Enlace expirado o inválido.']);
        }

        $header = ffInventoryMovement::where('folio', $folio)->first();

        if (!$header || $header->status !== 'pending') {
             return view('friends-and-family.orders.email-response', ['status' => 'error', 'message' => 'Este pedido ya fue procesado.']);
        }

        try {
            DB::beginTransaction();
            
            ffInventoryMovement::where('folio', $folio)->update([
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);
            
            // Trigger WMS Sync
            $syncService->syncOutboundOrderFromFolio($folio);
            
            $movements = ffInventoryMovement::where('folio', $folio)
                ->where('quantity', '<', 0)
                ->with(['product', 'quality'])
                ->get();

            if ($movements->isNotEmpty() && !empty($header->notification_emails)) {
                
                $emailType = $header->was_edited ? 'update' : 'new';

                $companyInfo = $this->getCompanyInfo($header->area_id);
                
                $pdfItems = [];
                $grandTotal = 0;
                
                foreach($movements as $m) {
                    $basePrice = $m->product->unit_price;
                    $discountPercent = $m->discount_percentage ?? 0;
                    $discountAmount = 0;
                    $finalPrice = 0;

                    if ($m->order_type === 'normal') {
                        $discountAmount = $basePrice * ($discountPercent / 100);
                        $finalPrice = $basePrice - $discountAmount;
                    } else {
                        $basePrice = 0;
                        $discountPercent = 0;
                        $discountAmount = 0;
                        $finalPrice = 0;
                    }

                    $totalLine = abs($m->quantity) * $finalPrice;
                    $grandTotal += $totalLine;

                    $pdfItems[] = [
                        'sku' => $m->product->sku,
                        'description' => $m->product->description,
                        'quality' => $m->quality ? $m->quality->name : 'Estándar',
                        'quantity' => abs($m->quantity),
                        'base_price' => $basePrice,
                        'discount_percentage' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'unit_price' => $finalPrice,
                        'total_price' => $totalLine,
                    ];
                }

                $logoUrl = $this->getLogoUrl($header->area_id);
                
                $pdfData = array_merge([
                    'items' => $pdfItems,
                    'grandTotal' => $grandTotal,
                    'folio' => $folio,
                    'date' => $header->created_at->format('d/m/Y'),
                    'client_name' => $header->client_name,
                    'company_name' => $header->company_name,
                    'client_phone' => $header->client_phone,
                    'address' => $header->address,
                    'locality' => $header->locality,
                    'delivery_date' => $header->delivery_date ? $header->delivery_date->format('d/m/Y H:i') : '',
                    'surtidor_name' => $header->surtidor_name,
                    'observations' => $header->observations,
                    'vendedor_name' => $header->user->name ?? 'N/A',
                    'logo_url' => $logoUrl,
                    'order_type' => $header->order_type,
                ], $companyInfo);

                $dompdf = new Dompdf();
                $dompdf->set_option('isRemoteEnabled', true);
                $pdfView = view('friends-and-family.sales.pdf', $pdfData);
                $dompdf->loadHtml($pdfView->render());
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $pdfContent = $dompdf->output();

                $stream = fopen('php://temp', 'r+');
                fputcsv($stream, ['SKU', 'Descripcion', 'Calidad', 'Cantidad', 'Precio Unitario', 'Total']);
                foreach ($pdfItems as $row) {
                    fputcsv($stream, [$row['sku'], $row['description'], $row['quality'], $row['quantity'], $row['unit_price'], $row['total_price']]);
                }
                rewind($stream);
                $csvContent = stream_get_contents($stream);
                fclose($stream);

                $conditionsPdfContent = null;
                if ($header->ff_client_id) {
                    $client = FfClient::with('deliveryConditions')->find($header->ff_client_id);
                    if ($client && $client->deliveryConditions && method_exists(FfAdministrationController::class, 'getPrepFieldsStatic')) {
                        $condData = [
                            'client' => $client,
                            'conditions' => $client->deliveryConditions,
                            'logoUrl' => $logoUrl,
                            'specific_address' => $header->address . ', ' . $header->locality,
                            'specific_observations' => $header->observations,
                            'prepFields' => FfAdministrationController::getPrepFieldsStatic(), 
                            'docFields' => FfAdministrationController::getDocFieldsStatic(),
                            'evidFields' => FfAdministrationController::getEvidFieldsStatic(),
                        ];
                        $pdfCond = Pdf::loadView('friends-and-family.admin.conditions-pdf', $condData);
                        $pdfCond->setPaper('A4', 'portrait');
                        $pdfCond->setOption('isRemoteEnabled', true);
                        $conditionsPdfContent = $pdfCond->output();
                    }
                }

                $allDocs = FfOrderDocument::where('folio', $folio)->get()->map(function($doc) {
                    return ['path' => $doc->path, 'name' => $doc->filename];
                })->toArray();

                $recipients = array_filter(array_map('trim', explode(',', str_replace(';', ',', $header->notification_emails))));
                
                if (!empty($recipients)) {
                    Mail::to($recipients)->send(new OrderActionMail(
                        $pdfData, 
                        $emailType,
                        $pdfContent, 
                        $csvContent, 
                        $allDocs,
                        $conditionsPdfContent
                    ));
                }
            }

            DB::commit();

            return view('friends-and-family.orders.email-response', [
                'status' => 'success',
                'message' => "El pedido #$folio ha sido APROBADO exitosamente. Se han enviado las notificaciones correspondientes."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Error aprobación email: " . $e->getMessage());
            return view('friends-and-family.orders.email-response', [
                'status' => 'error',
                'message' => 'Error interno al procesar la aprobación: ' . $e->getMessage()
            ]);
        }
    }

    public function emailRejectForm($folio, $adminId)
    {
        if (!request()->hasValidSignature()) {
            return view('friends-and-family.orders.email-response', [
                'status' => 'error',
                'message' => 'El enlace ha expirado o no es válido.'
            ]);
        }

        $header = ffInventoryMovement::where('folio', $folio)->where('status', 'pending')->first();
        
        if (!$header) {
            return view('friends-and-family.orders.email-response', [
                'status' => 'error',
                'message' => 'Este pedido no está disponible para rechazo (ya fue procesado).'
            ]);
        }

        return view('friends-and-family.orders.email-reject-form', compact('folio', 'adminId'));
    }

    public function emailRejectSubmit(Request $request, $folio, $adminId)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $header = ffInventoryMovement::where('folio', $folio)->where('status', 'pending')->first();

        if (!$header) {
            return view('friends-and-family.orders.email-response', [
                'status' => 'error',
                'message' => 'El pedido ya no está en estatus pendiente.'
            ]);
        }

        DB::beginTransaction();
        try {
            ffInventoryMovement::where('folio', $folio)->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);

            $movements = ffInventoryMovement::where('folio', $folio)->where('quantity', '<', 0)->get();
            
            foreach($movements as $mov) {
                ffInventoryMovement::create([
                    'ff_product_id' => $mov->ff_product_id,
                    'user_id' => $mov->user_id, 
                    'area_id' => $mov->area_id,
                    'quantity' => abs($mov->quantity),
                    'reason' => 'RECHAZO (Email) Pedido #' . $folio . ': ' . $request->reason,
                    'folio' => $folio,
                    'status' => 'rejected',
                    'client_name' => $mov->client_name,
                    'order_type' => $mov->order_type,
                    'ff_quality_id' => $mov->ff_quality_id,
                ]);
            }

            DB::commit();
            
            return view('friends-and-family.orders.email-response', [
                'status' => 'success',
                'message' => 'Pedido #' . $folio . ' RECHAZADO correctamente. El stock ha sido restaurado.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return view('friends-and-family.orders.email-response', [
                'status' => 'error',
                'message' => 'Error al rechazar: ' . $e->getMessage()
            ]);
        }
    }

    public function uploadBatchEvidences(Request $request, $folio)
    {
        $check = ffInventoryMovement::where('folio', $folio)->firstOrFail();
        
        if (!Auth::user()->isSuperAdmin() && $check->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para subir evidencias a este pedido.');
        }

        $request->validate([
            'evidences' => 'required|array',
            'evidences.*' => 'file|mimes:jpg,jpeg,png,pdf,zip,rar|max:20480',
        ]);

        $count = 0;

        if ($request->hasFile('evidences')) {
            foreach ($request->file('evidences') as $file) {
                $filename = $file->getClientOriginalName();
                $path = $file->store("ff_evidence/{$folio}", 's3');

                \App\Models\FfOrderEvidence::create([
                    'folio' => $folio,
                    'filename' => $filename,
                    'path' => $path,
                    'uploaded_by' => Auth::id()
                ]);
                
                $count++;
            }
        }

        return redirect()->back()->with('success', "Se han subido $count evidencias correctamente.");
    }

    public function deleteEvidence($id)
    {
        $evidence = \App\Models\FfOrderEvidence::findOrFail($id);
        
        $movement = ffInventoryMovement::where('folio', $evidence->folio)->first();
        if (!Auth::user()->isSuperAdmin() && $movement && $movement->area_id !== Auth::user()->area_id) {
            abort(403);
        }

        if (Storage::disk('s3')->exists($evidence->path)) {
            Storage::disk('s3')->delete($evidence->path);
        }

        $evidence->delete();

        return redirect()->back()->with('success', 'Evidencia eliminada.');
    }

    public function downloadEvidence(Request $request)
    {
        $path = $request->query('path');
        
        if (!$path) {
            abort(404);
        }

        $evidence = \App\Models\FfOrderEvidence::where('path', $path)->first();

        if ($evidence) {
            $order = ffInventoryMovement::where('folio', $evidence->folio)->first();
            
            if ($order && !Auth::user()->isSuperAdmin() && $order->area_id !== Auth::user()->area_id) {
                abort(403, 'No tienes permiso para ver esta evidencia.');
            }
        } 
        else {
            $exists = ffInventoryMovement::where(function($q) use ($path) {
                $q->where('evidence_path_1', $path)
                  ->orWhere('evidence_path_2', $path)
                  ->orWhere('evidence_path_3', $path);
            });

            if (!Auth::user()->isSuperAdmin()) {
                $exists->where('area_id', Auth::user()->area_id);
            }
            
            if (!$exists->exists()) {
                abort(403, 'Archivo no registrado en el sistema.');
            }
        }

        if (!Storage::disk('s3')->exists($path)) {
            abort(404, 'El archivo físico no existe en la nube.');
        }

        return Storage::disk('s3')->download($path);
    }

    public function downloadReport($folio)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $movements = ffInventoryMovement::where('folio', $folio)
            ->where('quantity', '<', 0)
            ->with(['product', 'warehouse', 'quality'])
            ->get();

        if ($movements->isEmpty()) return redirect()->back()->with('error', 'Pedido no encontrado.');

        $header = $movements->first();

        if (!Auth::user()->isSuperAdmin() && $header->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para ver este reporte.');
        }

        $evidences = \App\Models\FfOrderEvidence::where('folio', $folio)->get();

        $tempDir = storage_path('app/public/temp_report_' . uniqid());
        if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

        $processedEvidences = [];
        foreach ($evidences as $ev) {
            $localPath = null;
            $extension = strtolower(pathinfo($ev->filename, PATHINFO_EXTENSION));
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp']);
            
            $remoteUrl = Storage::disk('s3')->url($ev->path); 

            if ($isImage && Storage::disk('s3')->exists($ev->path)) {
                $content = Storage::disk('s3')->get($ev->path);
                $tempName = uniqid() . '_' . $ev->filename;
                $localPath = $tempDir . '/' . $tempName;
                file_put_contents($localPath, $content);
            }

            $processedEvidences[] = [
                'filename' => $ev->filename,
                'local_path' => $localPath,
                'remote_url' => $remoteUrl,
                'is_image' => $isImage,
                'uploaded_at' => $ev->created_at
            ];
        }

        $areaLogoKey = null;
        if($header->area_id) {
            $area = Area::find($header->area_id);
            if($area && $area->icon_path && Storage::disk('s3')->exists($area->icon_path)) {
                $areaLogoKey = $area->icon_path;
            }
        }
        $localAreaLogo = null;
        if ($areaLogoKey) {
            $content = Storage::disk('s3')->get($areaLogoKey);
            $localAreaLogo = $tempDir . '/area_logo.' . pathinfo($areaLogoKey, PATHINFO_EXTENSION);
            file_put_contents($localAreaLogo, $content);
        }

        $systemLogoKey = 'LogoBlanco1.PNG';
        $localSystemLogo = null;
        if(Storage::disk('s3')->exists($systemLogoKey)){
            $content = Storage::disk('s3')->get($systemLogoKey);
            $localSystemLogo = $tempDir . '/system_logo.png';
            file_put_contents($localSystemLogo, $content);
        }

        $pdfLogoKey = 'pdf-logo.png'; 
        $localPdfLogo = null;
        if(Storage::disk('s3')->exists($pdfLogoKey)){
            $content = Storage::disk('s3')->get($pdfLogoKey);
            $localPdfLogo = $tempDir . '/pdf_logo.png';
            file_put_contents($localPdfLogo, $content);
        }

        $watermarkKey = 'LogoAzulm.PNG';
        $localWatermark = null;
        if(Storage::disk('s3')->exists($watermarkKey)){
            $content = Storage::disk('s3')->get($watermarkKey);
            $localWatermark = $tempDir . '/watermark_logo.png';
            file_put_contents($localWatermark, $content);
        }

        $mainLogo = $localAreaLogo ?? $localSystemLogo;
        $companyInfo = $this->getCompanyInfo($header->area_id);
        
        $data = [
            'header' => $header,
            'items' => $movements,
            'evidences' => collect($processedEvidences),
            'logo_path' => $mainLogo,
            'system_logo' => $localSystemLogo,
            'pdf_logo' => $localPdfLogo,
            'watermark_logo' => $localWatermark,
            'company' => $companyInfo,
            'date' => now()
        ];

        $pdf = Pdf::loadView('friends-and-family.orders.report-evidence', $data);
        $pdf->setPaper('A4', 'landscape'); 
        $output = $pdf->output();

        array_map('unlink', glob("$tempDir/*.*"));
        rmdir($tempDir);

        return response()->streamDownload(
            fn () => print($output),
            'Entregable_Folio_'.$folio.'.pdf'
        );
    }
}