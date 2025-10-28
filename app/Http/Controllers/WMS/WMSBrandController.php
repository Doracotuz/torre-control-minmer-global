<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class WMSBrandController extends Controller 
{
    public function index()
    {
        $brands = Brand::latest()->paginate(15);
        return view('wms.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('wms.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
        ]);

        Brand::create($request->all());

        return redirect()->route('wms.brands.index')
                         ->with('success', 'Marca creada exitosamente.');
    }

    public function edit(Brand $brand)
    {
        return view('wms.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
        ]);

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