<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\Quality;
use App\Models\Area;
use Illuminate\Http\Request;

class WMSQualityController extends Controller
{
    public function index(Request $request)
    {
        $query = Quality::with('area')->latest();

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $qualities = $query->paginate(15)->withQueryString();
        $areas = Area::orderBy('name')->get();

        return view('wms.qualities.index', compact('qualities', 'areas'));
    }

    public function create()
    {
        $areas = Area::orderBy('name')->get();
        return view('wms.qualities.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
        ]);

        $exists = Quality::where('name', $request->name)
                         ->where('area_id', $request->area_id)
                         ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Ya existe una calidad con este nombre para el cliente seleccionado.'])->withInput();
        }

        Quality::create($request->all());

        return redirect()->route('wms.qualities.index')
                         ->with('success', 'Calidad creada exitosamente.');
    }

    public function edit(Quality $quality)
    {
        $areas = Area::orderBy('name')->get();
        return view('wms.qualities.edit', compact('quality', 'areas'));
    }

    public function update(Request $request, Quality $quality)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
        ]);

        $exists = Quality::where('name', $request->name)
                         ->where('area_id', $request->area_id)
                         ->where('id', '!=', $quality->id)
                         ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Ya existe una calidad con este nombre para el cliente seleccionado.'])->withInput();
        }

        $quality->update($request->all());

        return redirect()->route('wms.qualities.index')
                         ->with('success', 'Calidad actualizada exitosamente.');
    }

    public function destroy(Quality $quality)
    {
        try {
            $quality->delete();
            return redirect()->route('wms.qualities.index')
                             ->with('success', 'Calidad eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('wms.qualities.index')
                             ->with('error', 'No se puede eliminar la calidad, puede estar en uso.');
        }
    }
}