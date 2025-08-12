<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsBrand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Muestra la vista para gestionar las marcas.
     */
    public function index()
    {
        $brands = CsBrand::orderBy('name')->paginate(20);
        return view('customer-service.brands.index', compact('brands'));
    }

    /**
     * Guarda una nueva marca en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:cs_brands,name']);
        CsBrand::create($request->only('name'));
        return back()->with('success', 'Marca creada exitosamente.');
    }

    /**
     * Elimina una marca.
     */
    public function destroy(CsBrand $brand)
    {
        // Previene la eliminación si la marca tiene productos asociados
        if ($brand->products()->exists()) {
            return back()->with('error', 'No se puede eliminar la marca porque tiene productos asociados.');
        }
        $brand->delete();
        return back()->with('success', 'Marca eliminada exitosamente.');
    }
}
