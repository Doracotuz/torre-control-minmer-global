<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::orderBy('name')->paginate(10);
        return view('asset-management.sites.index', compact('sites'));
    }

    public function create()
    {
        return view('asset-management.sites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sites,name',
            'address' => 'nullable|string|max:255',
        ]);

        Site::create($validated);

        return redirect()->route('asset-management.sites.index')->with('success', 'Sitio creado exitosamente.');
    }

    public function edit(Site $site)
    {
        return view('asset-management.sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sites,name,' . $site->id,
            'address' => 'nullable|string|max:255',
        ]);

        $site->update($validated);

        return redirect()->route('asset-management.sites.index')->with('success', 'Sitio actualizado exitosamente.');
    }

    public function destroy(Site $site)
    {
        if ($site->assets()->exists()) {
             return back()->with('error', 'No se puede eliminar un sitio que tiene activos asignados.');
        }

        $site->delete();
        return redirect()->route('asset-management.sites.index')->with('success', 'Sitio eliminado exitosamente.');
    }
}