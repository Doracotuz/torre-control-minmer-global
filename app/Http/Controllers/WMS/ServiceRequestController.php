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
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\SalesOrder;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        // 1. Service Requests
        $srQuery = ServiceRequest::with(['area', 'warehouse', 'user']);
        if ($request->filled('area_id')) $srQuery->where('area_id', $request->area_id);
        if ($request->filled('warehouse_id')) $srQuery->where('warehouse_id', $request->warehouse_id);
        if ($request->filled('status')) $srQuery->where('status', $request->status);
        if ($request->filled('start_date')) $srQuery->whereDate('requested_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $srQuery->whereDate('requested_at', '<=', $request->end_date);
        
        $serviceRequests = $srQuery->latest()->limit(100)->get(); // Limit for performance

        // 2. Purchase Orders with Services
        $poQuery = PurchaseOrder::has('valueAddedServices')->with(['area', 'warehouse', 'user']);
        if ($request->filled('area_id')) $poQuery->where('area_id', $request->area_id);
        if ($request->filled('warehouse_id')) $poQuery->where('warehouse_id', $request->warehouse_id);
        if ($request->filled('status')) $poQuery->where('status', $request->status); // Status names might differ
        if ($request->filled('start_date')) $poQuery->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $poQuery->whereDate('created_at', '<=', $request->end_date);

        $purchaseOrders = $poQuery->latest()->limit(100)->get();

        // 3. Sales Orders with Services
        $soQuery = SalesOrder::has('valueAddedServices')->with(['area', 'warehouse', 'user']);
        if ($request->filled('area_id')) $soQuery->where('area_id', $request->area_id);
        if ($request->filled('warehouse_id')) $soQuery->where('warehouse_id', $request->warehouse_id);
        if ($request->filled('status')) $soQuery->where('status', $request->status);
        if ($request->filled('start_date')) $soQuery->whereDate('order_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $soQuery->whereDate('order_date', '<=', $request->end_date);

        $salesOrders = $soQuery->latest()->limit(100)->get();

        // Merge and Normalize
        $all = collect();

        foreach($serviceRequests as $item) {
            $item->source_type = 'Service Request';
            $item->display_folio = $item->folio;
            $item->display_date = $item->requested_at;
            $item->show_route = route('wms.service-requests.show', $item);
            $item->pdf_route = route('wms.service-requests.pdf', $item);
            $item->source_badge = 'bg-gray-100 text-gray-600';
            $all->push($item);
        }

        foreach($purchaseOrders as $item) {
            $item->source_type = 'Purchase Order';
            $item->display_folio = $item->po_number;
            $item->display_date = $item->created_at;
            $item->show_route = route('wms.purchase-orders.show', $item);
            $item->pdf_route = null;
            $item->source_badge = 'bg-blue-50 text-blue-600 border border-blue-100';
            $all->push($item);
        }

        foreach($salesOrders as $item) {
            $item->source_type = 'Sales Order';
            $item->display_folio = $item->so_number;
            $item->display_date = $item->order_date;
            $item->show_route = route('wms.sales-orders.show', $item);
            $item->pdf_route = null;
            $item->source_badge = 'bg-purple-50 text-purple-600 border border-purple-100';
            $all->push($item);
        }

        $sorted = $all->sortByDesc('display_date');

        // Manual Pagination
        $page = $request->input('page', 1);
        $perPage = 15;
        $offset = ($page * $perPage) - $perPage;
        
        $itemsForCurrentPage = $sorted->slice($offset, $perPage)->values();
        
        $requests = new LengthAwarePaginator(
            $itemsForCurrentPage, 
            $sorted->count(), 
            $perPage, 
            $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'total' => ServiceRequest::count(), // Keep strictly SR stats or sum all? keeping SR for now as asked context implies listing them
            'pending' => ServiceRequest::where('status', 'pending')->count(),
            'completed' => ServiceRequest::where('status', 'completed')->count(),
            'invoiced' => ServiceRequest::where('status', 'invoiced')->count(),
        ];

        $clients = Area::where('is_client', true)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('wms.service_requests.index', compact('requests', 'stats', 'clients', 'warehouses'));
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
