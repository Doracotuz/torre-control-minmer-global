<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\ServiceRequest;
use App\Models\Area;
use App\Models\Warehouse;
use App\Models\WMS\ValueAddedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::with(['area', 'warehouse', 'user'])
            ->latest()
            ->paginate(15);
        
        $stats = [
            'total' => ServiceRequest::count(),
            'pending' => ServiceRequest::where('status', 'pending')->count(),
            'completed' => ServiceRequest::where('status', 'completed')->count(),
            'invoiced' => ServiceRequest::where('status', 'invoiced')->count(),
        ];
        
        return view('wms.service_requests.index', compact('requests', 'stats'));
    }

    public function create()
    {
        $clients = Area::where('is_client', true)->get();
        // Assuming warehouses are available globally or filtered by permissions
        $warehouses = Warehouse::all(); 
        
        return view('wms.service_requests.create', compact('clients', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        // Generate Folio
        $lastRequest = ServiceRequest::latest()->first();
        $sequence = $lastRequest ? intval(substr($lastRequest->folio, -5)) + 1 : 1;
        $folio = 'SR-' . date('Y') . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        $serviceRequest = ServiceRequest::create([
            'folio' => $folio,
            'area_id' => $validated['area_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'user_id' => Auth::id(),
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return redirect()->route('wms.service-requests.show', $serviceRequest)
            ->with('success', 'Solicitud de servicio creada corréctamente.');
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['valueAddedServices.service', 'area', 'warehouse', 'user']);
        $services = ValueAddedService::all(); // For the dropdown to add services
        
        return view('wms.service_requests.show', compact('serviceRequest', 'services'));
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,invoiced,cancelled',
        ]);

        $data = ['status' => $validated['status']];
        
        if ($validated['status'] === 'completed' && !$serviceRequest->completed_at) {
            $data['completed_at'] = now();
        }

        $serviceRequest->update($data);

        return redirect()->back()->with('success', 'Estatus actualizado corréctamente.');
    }

    public function destroy(ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Solo se pueden eliminar solicitudes pendientes.');
        }
        
        $serviceRequest->delete();
        return redirect()->route('wms.service-requests.index')->with('success', 'Solicitud eliminada corréctamente.');
    }

    public function pdf(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['valueAddedServices.service', 'area', 'warehouse', 'user']);
        
        $pdf = Pdf::loadView('wms.service_requests.pdf', compact('serviceRequest'));
        return $pdf->stream("Solicitud-{$serviceRequest->folio}.pdf");
    }
}
