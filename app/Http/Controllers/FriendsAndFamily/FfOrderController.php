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

class FfOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ffInventoryMovement::query()
            ->where('quantity', '<', 0);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
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
                DB::raw('SUM(ABS(quantity)) as total_items'),
                DB::raw('MAX(id) as id'),
                DB::raw('MAX(CASE WHEN is_backorder = 1 AND backorder_fulfilled = 0 THEN 1 ELSE 0 END) as has_active_backorder')
            )
            ->groupBy('folio', 'client_name', 'company_name', 'order_type', 'status', 'delivery_date', 'created_at', 'user_id', 'area_id');

        if ($request->filled('folio')) {
            $query->where('folio', 'like', "%{$request->folio}%");
        }
        if ($request->filled('client')) {
            $query->where('client_name', 'like', "%{$request->client}%")
                  ->orWhere('company_name', 'like', "%{$request->client}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('order_type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('show_backorders')) {
            $query->havingRaw('MAX(CASE WHEN is_backorder = 1 AND backorder_fulfilled = 0 THEN 1 ELSE 0 END) = 1');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('friends-and-family.orders.index', compact('orders'));
    }

    public function show($folio)
    {
        $movements = ffInventoryMovement::where('folio', $folio)
            ->where('quantity', '<', 0)
            ->with(['product', 'user', 'approver'])
            ->get();

        if ($movements->isEmpty()) {
            return redirect()->route('ff.orders.index')->with('error', 'Pedido no encontrado');
        }

        $header = $movements->first();

        if (!Auth::user()->isSuperAdmin() && $header->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para ver este pedido.');
        }
        
        $totalItems = $movements->sum(fn($m) => abs($m->quantity));
        $totalValue = $movements->sum(fn($m) => abs($m->quantity) * ($m->product->unit_price * (1 - ($m->discount_percentage/100))));

        if ($header->order_type !== 'normal') $totalValue = 0;

        return view('friends-and-family.orders.show', compact('header', 'movements', 'totalItems', 'totalValue'));
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

    public function approve($folio)
    {
        $this->authorizeAdmin();

        $check = ffInventoryMovement::where('folio', $folio)->firstOrFail();
        if (!Auth::user()->isSuperAdmin() && $check->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para aprobar este pedido.');
        }

        ffInventoryMovement::where('folio', $folio)->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $movements = ffInventoryMovement::where('folio', $folio)
            ->where('quantity', '<', 0)
            ->with('product')
            ->get();

        if ($movements->isNotEmpty()) {
            $header = $movements->first();
            $companyInfo = $this->getCompanyInfo($header->area_id);
            
            if (!empty($header->notification_emails)) {
                try {
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
                    fputcsv($stream, ['SKU', 'Descripcion', 'Cantidad', 'Precio Unitario', 'Total']);
                    foreach ($pdfItems as $row) {
                        fputcsv($stream, [$row['sku'], $row['description'], $row['quantity'], $row['unit_price'], $row['total_price']]);
                    }
                    rewind($stream);
                    $csvContent = stream_get_contents($stream);
                    fclose($stream);

                    $conditionsPdfContent = null;
                    if ($header->ff_client_id) {
                        $client = FfClient::with('deliveryConditions')->find($header->ff_client_id);
                        if ($client && $client->deliveryConditions) {
                            $condData = [
                                'client' => $client,
                                'conditions' => $client->deliveryConditions,
                                // 'logoUrl' => Storage::disk('s3')->url('LogoAzulm.PNG'),
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

                    $recipients = array_filter(array_map('trim', explode(';', $header->notification_emails)));
                    
                    if (!empty($recipients)) {
                        Mail::to($recipients)->send(new OrderActionMail(
                            $pdfData, 
                            'new', 
                            $pdfContent, 
                            $csvContent, 
                            $allDocs,
                            $conditionsPdfContent
                        ));
                    }

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error enviando correo de aprobación: " . $e->getMessage());
                }
            }
        }

        return redirect()->back()->with('success', 'Pedido #' . $folio . ' APROBADO y notificaciones enviadas.');
    }

    public function reject(Request $request, $folio)
    {
        $this->authorizeAdmin();
        
        $check = ffInventoryMovement::where('folio', $folio)->firstOrFail();
        if (!Auth::user()->isSuperAdmin() && $check->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para rechazar este pedido.');
        }

        $request->validate(['reason' => 'required|string|max:255']);

        DB::beginTransaction();
        try {
            ffInventoryMovement::where('folio', $folio)->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            $movements = ffInventoryMovement::where('folio', $folio)->where('quantity', '<', 0)->get();
            
            foreach($movements as $mov) {
                ffInventoryMovement::create([
                    'ff_product_id' => $mov->ff_product_id,
                    'user_id' => Auth::id(),
                    'area_id' => $mov->area_id,
                    'quantity' => abs($mov->quantity),
                    'reason' => 'RECHAZO Pedido #' . $folio . ': ' . $request->reason,
                    'folio' => $folio,
                    'status' => 'rejected',
                    'client_name' => $mov->client_name
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pedido #' . $folio . ' RECHAZADO y stock restaurado.');

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

    public function emailApprove($folio, $adminId)
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
            
            $movements = ffInventoryMovement::where('folio', $folio)
                ->where('quantity', '<', 0)
                ->with('product')
                ->get();

            if ($movements->isNotEmpty() && !empty($header->notification_emails)) {
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
                fputcsv($stream, ['SKU', 'Descripcion', 'Cantidad', 'Precio Unitario', 'Total']);
                foreach ($pdfItems as $row) {
                    fputcsv($stream, [$row['sku'], $row['description'], $row['quantity'], $row['unit_price'], $row['total_price']]);
                }
                rewind($stream);
                $csvContent = stream_get_contents($stream);
                fclose($stream);

                $conditionsPdfContent = null;
                if ($header->ff_client_id) {
                    $client = FfClient::with('deliveryConditions')->find($header->ff_client_id);
                    if ($client && $client->deliveryConditions) {
                        $condData = [
                            'client' => $client,
                            'conditions' => $client->deliveryConditions,
                            // 'logoUrl' => Storage::disk('s3')->url('LogoAzulm.PNG'),
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

                $recipients = array_filter(array_map('trim', explode(';', $header->notification_emails)));
                
                if (!empty($recipients)) {
                    Mail::to($recipients)->send(new OrderActionMail(
                        $pdfData, 
                        'new', 
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
            'evidence_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'evidence_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'evidence_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $updates = [];
        $hasUploads = false;

        for ($i = 1; $i <= 3; $i++) {
            $inputName = "evidence_{$i}";
            
            if ($request->hasFile($inputName)) {
                $path = $request->file($inputName)->store("ff_evidence/{$folio}", 's3');
                $updates["evidence_path_{$i}"] = $path;
                $hasUploads = true;
            }
        }

        if ($hasUploads) {
            ffInventoryMovement::where('folio', $folio)->update($updates);
            return redirect()->back()->with('success', 'Evidencias guardadas correctamente.');
        }

        return redirect()->back()->with('warning', 'No se seleccionó ningún archivo para subir.');
    }

    public function downloadEvidence(Request $request)
    {
        $path = $request->query('path');
        
        if (!$path) {
            abort(404);
        }

        $exists = ffInventoryMovement::where(function($q) use ($path) {
            $q->where('evidence_path_1', $path)
              ->orWhere('evidence_path_2', $path)
              ->orWhere('evidence_path_3', $path);
        });

        if (!Auth::user()->isSuperAdmin()) {
            $exists->where('area_id', Auth::user()->area_id);
        }
        
        if (!$exists->exists()) {
            abort(403, 'Archivo no encontrado o sin permisos.');
        }

        if (!Storage::disk('s3')->exists($path)) {
            abort(404);
        }

        return Storage::disk('s3')->download($path);
    }    
}