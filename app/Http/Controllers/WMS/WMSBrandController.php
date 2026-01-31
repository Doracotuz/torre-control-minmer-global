<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Area;
use Illuminate\Http\Request;

class WMSBrandController extends Controller 
{
    public function index(Request $request)
    {
        $query = Brand::with('area')->latest();

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $brands = $query->paginate(15)->withQueryString();
        $areas = Area::orderBy('name')->get();

        return view('wms.brands.index', compact('brands', 'areas'));
    }

    public function create()
    {
        $areas = Area::orderBy('name')->get();
        return view('wms.brands.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
        ]);

        $exists = Brand::where('name', $request->name)
                       ->where('area_id', $request->area_id)
                       ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Ya existe una marca con este nombre para el cliente seleccionado.'])->withInput();
        }

        Brand::create($request->all());

        return redirect()->route('wms.brands.index')
                         ->with('success', 'Marca creada exitosamente.');
    }

    public function edit(Brand $brand)
    {
        $areas = Area::orderBy('name')->get();
        return view('wms.brands.edit', compact('brand', 'areas'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
        ]);

        $exists = Brand::where('name', $request->name)
                       ->where('area_id', $request->area_id)
                       ->where('id', '!=', $brand->id)
                       ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Ya existe una marca con este nombre para el cliente seleccionado.'])->withInput();
        }

        $brand->update($request->all());

        return redirect()->route('wms.brands.index')
                         ->with('success', 'Marca actualizada exitosamente.');
    }

    public function destroy(Brand $brand)
    {
        try {
            $brand->delete();
            return redirect()->route('wms.brands.index')
                             ->with('success', 'Marca eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('wms.brands.index')
                             ->with('error', 'No se puede eliminar la marca porque está asociada a uno o más productos.');
        }
    }
}