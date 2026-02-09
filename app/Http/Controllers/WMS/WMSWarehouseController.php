<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WMSWarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasFfPermission('wms.warehouses')) {
                abort(403, 'No tienes permiso para gestionar almacenes.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $warehouses = Warehouse::latest()->paginate(15);
        return view('wms.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('wms.warehouses.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name',
            'code' => 'required|string|max:10|unique:warehouses,code',
            'address' => 'nullable|string',
        ]);

        Warehouse::create($validatedData);

        return redirect()->route('wms.warehouses.index')
                        ->with('success', 'Almacén creado exitosamente.');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('wms.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name,' . $warehouse->id,
            'code' => 'required|string|max:10|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string',
        ]);

        $warehouse->update($validatedData);

        return redirect()->route('wms.warehouses.index')
                        ->with('success', 'Almacén actualizado exitosamente.');
    }


    public function destroy(Warehouse $warehouse)
    {
        try {
            $warehouse->delete();
            return redirect()->route('wms.warehouses.index')
                             ->with('success', 'Almacén eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('wms.warehouses.index')
                             ->with('error', 'No se puede eliminar el almacén porque tiene ubicaciones asociadas.');
        }
    }
}