<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Importar Storage

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
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación para el icono
        ]);

        $data = $request->all();

        if ($request->hasFile('icon')) {
            $data['icon_path'] = $request->file('icon')->store('area_icons', 'public');
        }

        Area::create($data);

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
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación para el icono
        ]);

        $data = $request->all();

        // Si se sube un nuevo icono
        if ($request->hasFile('icon')) {
            // Eliminar el icono antiguo si existe
            if ($area->icon_path && Storage::disk('public')->exists($area->icon_path)) {
                Storage::disk('public')->delete($area->icon_path);
            }
            $data['icon_path'] = $request->file('icon')->store('area_icons', 'public');
        } elseif ($request->input('remove_icon')) { // Si se marcó la casilla para eliminar el icono
            if ($area->icon_path && Storage::disk('public')->exists($area->icon_path)) {
                Storage::disk('public')->delete($area->icon_path);
            }
            $data['icon_path'] = null;
        }

        $area->update($data);

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
        // Lógica de eliminación en cascada para carpetas y sus contenidos
        // (Asumiendo que el modelo Folder tiene el evento 'deleting' que maneja FileLinks)
        $area->folders->each(function ($folder) {
            $folder->delete(); // Esto disparará el evento 'deleting' en el modelo Folder
        });

        // Eliminar el icono del área si existe
        if ($area->icon_path && Storage::disk('public')->exists($area->icon_path)) {
            Storage::disk('public')->delete($area->icon_path);
        }

        $area->delete(); // Eliminar el registro del área

        return redirect()->route('admin.areas.index')->with('success', 'Área eliminada exitosamente.');
    }
}