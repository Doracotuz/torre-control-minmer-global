<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductType;
use Illuminate\Http\Request;

class WMSProductController extends Controller
{
    public function index()
    {
        // Eager load relationships for better performance
        $products = Product::with(['brand', 'productType'])->latest()->paginate(10);
        return view('wms.products.index', compact('products'));
    }

    public function create()
    {
        // Get data for dropdowns
        $brands = Brand::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        return view('wms.products.create', compact('brands', 'productTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'unit_of_measure' => 'required|string|max:50',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255|unique:products,upc',
        ]);

        Product::create($request->all());

        return redirect()->route('wms.products.index')
                         ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Product $product)
    {
        $brands = Brand::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        return view('wms.products.edit', compact('product', 'brands', 'productTypes'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'unit_of_measure' => 'required|string|max:50',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255|unique:products,upc,' . $product->id,
        ]);

        $product->update($request->all());

        return redirect()->route('wms.products.index')
                         ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('wms.products.index')
                             ->with('success', 'Producto eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('wms.products.index')
                             ->with('error', 'No se puede eliminar el producto porque tiene inventario o está en un pedido.');
        }
    }
}