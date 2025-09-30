<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\Request;

class ManufacturerController extends Controller
{
    public function index()
    {
        $manufacturers = Manufacturer::orderBy('name')->paginate(10);
        return view('asset-management.manufacturers.index', compact('manufacturers'));
    }

    public function create()
    {
        return view('asset-management.manufacturers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:manufacturers,name',
        ]);

        Manufacturer::create($validated);
        return redirect()->route('asset-management.manufacturers.index')->with('success', 'Fabricante creado exitosamente.');
    }

    public function edit(Manufacturer $manufacturer)
    {
        return view('asset-management.manufacturers.edit', compact('manufacturer'));
    }

    public function update(Request $request, Manufacturer $manufacturer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:manufacturers,name,' . $manufacturer->id,
        ]);

        $manufacturer->update($validated);
        return redirect()->route('asset-management.manufacturers.index')->with('success', 'Fabricante actualizado exitosamente.');
    }

    public function destroy(Manufacturer $manufacturer)
    {
        $manufacturer->delete();
        return redirect()->route('asset-management.manufacturers.index')->with('success', 'Fabricante eliminado exitosamente.');
    }
}