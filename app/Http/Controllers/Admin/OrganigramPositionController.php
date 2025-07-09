<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganigramPosition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganigramPositionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $positions = OrganigramPosition::orderBy('hierarchy_level')->orderBy('name')->get();
        return view('admin.organigram.positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // No se necesita una vista 'create' separada si gestionas todo en el index con Alpine.js (como Activities/Skills)
        // Pero si prefieres una página de creación separada, puedes habilitar esto:
        // return view('admin.organigram.positions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organigram_positions,name',
            'description' => 'nullable|string|max:1000',
            'hierarchy_level' => 'nullable|integer|min:0', // Permite ordenar
        ]);

        OrganigramPosition::create($request->all());

        return redirect()->route('admin.organigram.positions.index')->with('success', 'Posición creada exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrganigramPosition  $organigramPosition
     * @return \Illuminate\Http\Response
     */
    public function show(OrganigramPosition $organigramPosition)
    {
        // No se necesita una vista 'show' para esta funcionalidad.
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrganigramPosition  $organigramPosition
     * @return \Illuminate\Http\Response
     */
    public function edit(OrganigramPosition $organigramPosition)
    {
        // No se necesita una vista 'edit' separada si gestionas todo en el index con Alpine.js.
        // Pero si prefieres una página de edición separada, puedes habilitar esto:
        // return view('admin.organigram.positions.edit', compact('organigramPosition'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrganigramPosition  $organigramPosition
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrganigramPosition $organigramPosition)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('organigram_positions', 'name')->ignore($organigramPosition->id)],
            'description' => 'nullable|string|max:1000',
            'hierarchy_level' => 'nullable|integer|min:0',
        ]);

        $organigramPosition->update($request->all());

        return redirect()->route('admin.organigram.positions.index')->with('success', 'Posición actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrganigramPosition  $organigramPosition
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrganigramPosition $organigramPosition)
    {
        // Antes de eliminar, considera si esta posición está asignada a algún miembro.
        // Si lo está, la clave foránea en organigram_members está configurada con onDelete('set null'),
        // así que los miembros que tengan esta posición la verán como NULA.
        $organigramPosition->delete();

        return redirect()->route('admin.organigram.positions.index')->with('success', 'Posición eliminada exitosamente.');
    }
}