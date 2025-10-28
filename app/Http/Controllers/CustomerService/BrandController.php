<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsBrand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = CsBrand::orderBy('name')->paginate(20);
        return view('customer-service.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:cs_brands,name']);
        CsBrand::create($request->only('name'));
        return back()->with('success', 'Marca creada exitosamente.');
    }

    public function destroy(CsBrand $brand)
    {
        if ($brand->products()->exists()) {
            return back()->with('error', 'No se puede eliminar la marca porque tiene productos asociados.');
        }
        $brand->delete();
        return back()->with('success', 'Marca eliminada exitosamente.');
    }
}
