<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use App\Models\Area;
use Illuminate\Http\Request;

class WMSProductTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductType::with('area')->latest();

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $productTypes = $query->paginate(15)->withQueryString();
        $areas = Area::orderBy('name')->get();

        return view('wms.product-types.index', compact('productTypes', 'areas'));
    }

    public function create()
    {
        $areas = Area::orderBy('name')->get();
        return view('wms.product-types.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
        ]);

        $exists = ProductType::where('name', $request->name)
                             ->where('area_id', $request->area_id)
                             ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Ya existe un tipo de producto con este nombre para el cliente seleccionado.'])->withInput();
        }

        ProductType::create($request->all());

        return redirect()->route('wms.product-types.index')
                         ->with('success', 'Tipo de producto creado exitosamente.');
    }

    public function edit(ProductType $productType)
    {
        $areas = Area::orderBy('name')->get();
        return view('wms.product-types.edit', compact('productType', 'areas'));
    }

    public function update(Request $request, ProductType $productType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
        ]);

        $exists = ProductType::where('name', $request->name)
                             ->where('area_id', $request->area_id)
                             ->where('id', '!=', $productType->id)
                             ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Ya existe un tipo de producto con este nombre para el cliente seleccionado.'])->withInput();
        }

        $productType->update($request->all());

        return redirect()->route('wms.product-types.index')
                         ->with('success', 'Tipo de producto actualizado exitosamente.');
    }

    public function destroy(ProductType $productType)
    {
        try {
            $productType->delete();
            return redirect()->route('wms.product-types.index')
                             ->with('success', 'Tipo de producto eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('wms.product-types.index')
                             ->with('error', 'No se puede eliminar porque está en uso por uno o más productos.');
        }
    }
}