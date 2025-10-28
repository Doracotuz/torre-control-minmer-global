<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganigramPosition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganigramPositionController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $positions = OrganigramPosition::orderBy('hierarchy_level')->orderBy('name')->get();
        return view('admin.organigram.positions.index', compact('positions'));
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organigram_positions,name',
            'description' => 'nullable|string|max:1000',
            'hierarchy_level' => 'nullable|integer|min:0',
        ]);

        OrganigramPosition::create($request->all());

        return redirect()->route('admin.organigram.positions.index')->with('success', 'Posición creada exitosamente.');
    }

    /**
     * @param  \App\Models\OrganigramPosition  $organigramPosition
     * @return \Illuminate\Http\Response
     */
    public function show(OrganigramPosition $organigramPosition)
    {
    }

    /**
     * @param  \App\Models\OrganigramPosition  $organigramPosition
     * @return \Illuminate\Http\Response
     */
    public function edit(OrganigramPosition $organigramPosition)
    {
    }

    /**
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
     * @param  \App\Models\OrganigramPosition  $organigramPosition
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrganigramPosition $organigramPosition)
    {
        $organigramPosition->delete();

        return redirect()->route('admin.organigram.positions.index')->with('success', 'Posición eliminada exitosamente.');
    }
}