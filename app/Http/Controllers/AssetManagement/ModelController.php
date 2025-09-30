<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareModel;
use App\Models\Manufacturer;
use App\Models\HardwareCategory;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    public function index()
    {
        $models = HardwareModel::with(['manufacturer', 'category'])->orderBy('name')->paginate(10);
        return view('asset-management.models.index', compact('models'));
    }

    public function create()
    {
        $manufacturers = Manufacturer::orderBy('name')->get();
        $categories = HardwareCategory::orderBy('name')->get();
        return view('asset-management.models.create', compact('manufacturers', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'hardware_category_id' => 'required|exists:hardware_categories,id',
        ]);

        HardwareModel::create($validated);
        return redirect()->route('asset-management.models.index')->with('success', 'Modelo creado exitosamente.');
    }

    public function edit(HardwareModel $model)
    {
        $manufacturers = Manufacturer::orderBy('name')->get();
        $categories = HardwareCategory::orderBy('name')->get();
        return view('asset-management.models.edit', compact('model', 'manufacturers', 'categories'));
    }

    public function update(Request $request, HardwareModel $model)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'hardware_category_id' => 'required|exists:hardware_categories,id',
        ]);

        $model->update($validated);
        return redirect()->route('asset-management.models.index')->with('success', 'Modelo actualizado exitosamente.');
    }

    public function destroy(HardwareModel $model)
    {
        if ($model->assets()->exists()) {
            return back()->with('error', 'No se puede eliminar un modelo que está en uso por uno o más activos.');
        }

        $model->delete();
        return redirect()->route('asset-management.models.index')->with('success', 'Modelo eliminado exitosamente.');
    }
}