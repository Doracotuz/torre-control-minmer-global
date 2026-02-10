<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\ValueAddedService;
use App\Models\WMS\ValueAddedServiceAssignment;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ValueAddedServiceController extends Controller
{
    public function index()
    {
        $services = ValueAddedService::all();
        return view('wms.value_added_services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:wms_value_added_services,code|max:255',
            'description' => 'required|string|max:255',
            'type' => 'required|in:consumable,service',
            'cost' => 'required|numeric|min:0',
        ]);

        ValueAddedService::create($validated);

        return redirect()->back()->with('success', 'Servicio/Consumible creado correctamente.');
    }

    public function update(Request $request, ValueAddedService $valueAddedService)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('wms_value_added_services')->ignore($valueAddedService->id)],
            'description' => 'required|string|max:255',
            'type' => 'required|in:consumable,service',
            'cost' => 'required|numeric|min:0',
        ]);

        $valueAddedService->update($validated);

        return redirect()->back()->with('success', 'Servicio/Consumible actualizado correctamente.');
    }

    public function destroy(ValueAddedService $valueAddedService)
    {
        $valueAddedService->delete();
        return redirect()->back()->with('success', 'Servicio/Consumible eliminado correctamente.');
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'value_added_service_id' => 'required|exists:wms_value_added_services,id',
            'assignable_id' => 'required|integer',
            'assignable_type' => 'required|string|in:purchase_order,sales_order,service_request',
            'quantity' => 'required|integer|min:1',
        ]);

        $service = ValueAddedService::findOrFail($validated['value_added_service_id']);
        
        $assignableType = match($validated['assignable_type']) {
            'purchase_order' => PurchaseOrder::class,
            'sales_order' => SalesOrder::class,
            'service_request' => \App\Models\WMS\ServiceRequest::class,
        };

        $model = $assignableType::findOrFail($validated['assignable_id']);

        $model->valueAddedServices()->create([
            'value_added_service_id' => $service->id,
            'quantity' => $validated['quantity'],
            'cost_snapshot' => $service->cost,
        ]);

        return redirect()->back()->with('success', 'Servicio asignado correctamente.');
    }

    public function detach(ValueAddedServiceAssignment $assignment)
    {
        $assignment->delete();
        return redirect()->back()->with('success', 'Asignación eliminada correctamente.');
    }
}
