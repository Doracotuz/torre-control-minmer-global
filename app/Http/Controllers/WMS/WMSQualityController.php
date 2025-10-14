<?php
namespace App\Http\Controllers\WMS;
use App\Http\Controllers\Controller;
use App\Models\WMS\Quality;
use Illuminate\Http\Request;

class WMSQualityController extends Controller
{
    public function index() {
        $qualities = Quality::latest()->paginate(15);
        return view('wms.qualities.index', compact('qualities'));
    }
    public function create() {
        return view('wms.qualities.create');
    }
    public function store(Request $request) {
        $request->validate(['name' => 'required|string|unique:qualities,name', 'description' => 'nullable|string']);
        Quality::create($request->all());
        return redirect()->route('wms.qualities.index')->with('success', 'Calidad creada exitosamente.');
    }
    public function edit(Quality $quality) {
        return view('wms.qualities.edit', compact('quality'));
    }
    public function update(Request $request, Quality $quality) {
        $request->validate(['name' => 'required|string|unique:qualities,name,'.$quality->id, 'description' => 'nullable|string']);
        $quality->update($request->all());
        return redirect()->route('wms.qualities.index')->with('success', 'Calidad actualizada exitosamente.');
    }
    public function destroy(Quality $quality) {
        try {
            $quality->delete();
            return redirect()->route('wms.qualities.index')->with('success', 'Calidad eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('wms.qualities.index')->with('error', 'No se puede eliminar la calidad, puede estar en uso.');
        }
    }
}