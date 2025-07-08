<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    /**
     * Display a listing of the areas.
     * Muestra una lista de todas las áreas.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $areas = Area::orderBy('name')->get();
        return view('admin.areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new area.
     * Muestra el formulario para crear una nueva área.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.areas.create');
    }

    /**
     * Store a newly created area in storage.
     * Almacena una nueva área en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Area::create($request->all());

        return redirect()->route('admin.areas.index')->with('success', 'Área creada exitosamente.');
    }

    /**
     * Show the form for editing the specified area.
     * Muestra el formulario para editar el área especificada.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\View\View
     */
    public function edit(Area $area)
    {
        return view('admin.areas.edit', compact('area'));
    }

    /**
     * Update the specified area in storage.
     * Actualiza el área especificada en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->ignore($area->id),
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        $area->update($request->all());

        return redirect()->route('admin.areas.index')->with('success', 'Área actualizada exitosamente.');
    }

    /**
     * Remove the specified area from storage.
     * Elimina el área especificada de la base de datos.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\RedirectResponse
     */
        public function destroy(Area $area)
        {
            // Primero, manejamos los usuarios de esta área
            // Esto los establecerá a null como ya lo hace tu migración de users.
            // Si quisieras eliminarlos, tendrías que hacer: $area->users()->delete();
            // Pero tu migración de users ya maneja onDelete('set null'), así que no hay que hacer nada aquí.

            // Eliminar todas las carpetas asociadas a esta área.
            // Al llamar a delete() en cada carpeta, se disparará el evento 'deleting' de Folder,
            // que a su vez eliminará los FileLinks y sus archivos físicos.
            $area->folders->each(function ($folder) {
                $folder->delete(); // Esto activará el evento 'deleting' en el modelo Folder
            });

            // Finalmente, eliminar el área.
            $area->delete();

            return redirect()->route('admin.areas.index')->with('success', 'Área eliminada exitosamente.');
        }
}
