<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Muestra una lista de todos los sitios.
     */
    public function index()
    {
        $sites = Site::orderBy('name')->paginate(10);
        return view('asset-management.sites.index', compact('sites'));
    }

    /**
     * Muestra el formulario para crear un nuevo sitio.
     */
    public function create()
    {
        return view('asset-management.sites.create');
    }

    /**
     * Almacena un nuevo sitio en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sites,name',
            'address' => 'nullable|string|max:255',
        ]);

        Site::create($validated);

        return redirect()->route('asset-management.sites.index')->with('success', 'Sitio creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un sitio existente.
     */
    public function edit(Site $site)
    {
        return view('asset-management.sites.edit', compact('site'));
    }

    /**
     * Actualiza un sitio en la base de datos.
     */
    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sites,name,' . $site->id,
            'address' => 'nullable|string|max:255',
        ]);

        $site->update($validated);

        return redirect()->route('asset-management.sites.index')->with('success', 'Sitio actualizado exitosamente.');
    }

    /**
     * Elimina un sitio de la base de datos.
     */
    public function destroy(Site $site)
    {
        if ($site->assets()->exists()) {
             return back()->with('error', 'No se puede eliminar un sitio que tiene activos asignados.');
        }

        $site->delete();
        return redirect()->route('asset-management.sites.index')->with('success', 'Sitio eliminado exitosamente.');
    }
}