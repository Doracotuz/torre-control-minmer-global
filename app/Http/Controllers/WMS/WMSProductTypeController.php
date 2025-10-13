<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use Illuminate\Http\Request;

class WMSProductTypeController extends Controller
{
    public function index()
    {
        $productTypes = ProductType::latest()->paginate(15);
        return view('wms.product-types.index', compact('productTypes'));
    }

    public function create()
    {
        return view('wms.product-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_types,name',
        ]);

        ProductType::create($request->all());

        return redirect()->route('wms.product-types.index')
                         ->with('success', 'Tipo de producto creado exitosamente.');
    }

    public function edit(ProductType $productType)
    {
        return view('wms.product-types.edit', compact('productType'));
    }

    public function update(Request $request, ProductType $productType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_types,name,' . $productType->id,
        ]);

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
                             ->with('error', 'No se puede eliminar el tipo porque está asociado a uno o más productos.');
        }
    }
}