<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WMSLocationController extends Controller
{
    public function index()
    {
        $locations = Location::with('warehouse')->latest()->paginate(15);
        return view('wms.locations.index', compact('locations'));
    }

    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('wms.locations.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        // El método validate() ahora devuelve solo los datos seguros
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string|max:255|unique:locations,code',
            'aisle' => 'nullable|string|max:255',
            'rack' => 'nullable|string|max:255',
            'shelf' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'type' => 'required|string',
        ]);

        // Usamos los datos validados
        Location::create($validatedData);

        return redirect()->route('wms.locations.index')
                        ->with('success', 'Ubicación creada exitosamente.');
    }

    public function edit(Location $location)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('wms.locations.edit', compact('location', 'warehouses'));
    }

    public function update(Request $request, Location $location)
    {
        // El método validate() ahora devuelve solo los datos seguros
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string|max:255|unique:locations,code,' . $location->id,
            'aisle' => 'nullable|string|max:255',
            'rack' => 'nullable|string|max:255',
            'shelf' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'type' => 'required|string',
        ]);

        // Usamos los datos validados
        $location->update($validatedData);

        return redirect()->route('wms.locations.index')
                        ->with('success', 'Ubicación actualizada exitosamente.');
    }

    public function destroy(Location $location)
    {
        try {
            $location->delete();
            return redirect()->route('wms.locations.index')
                             ->with('success', 'Ubicación eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('wms.locations.index')
                             ->with('error', 'No se puede eliminar la ubicación porque tiene inventario asociado.');
        }
    }
}