<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = HardwareCategory::orderBy('name')->paginate(10);
        return view('asset-management.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('asset-management.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hardware_categories,name',
        ]);

        HardwareCategory::create($validated);
        return redirect()->route('asset-management.categories.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(HardwareCategory $category)
    {
        return view('asset-management.categories.edit', compact('category'));
    }

    public function update(Request $request, HardwareCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hardware_categories,name,' . $category->id,
        ]);

        $category->update($validated);
        return redirect()->route('asset-management.categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(HardwareCategory $category)
    {
        if ($category->models()->exists()) {
             return back()->with('error', 'No se puede eliminar una categoría que tiene modelos asignados.');
        }

        $category->delete();
        return redirect()->route('asset-management.categories.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}