<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AreaController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $areas = Area::orderBy('name')->get();
        return view('admin.areas.index', compact('areas'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.areas.create');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'emitter_name' => 'nullable|string|max:255',
            'emitter_phone' => 'nullable|string|max:20',
            'emitter_address' => 'nullable|string|max:255',
            'emitter_colonia' => 'nullable|string|max:255',
            'emitter_cp' => 'nullable|string|max:10',
            'is_client' => 'boolean',            
        ]);

        $data = $request->all();

        $data['is_client'] = $request->has('is_client');

        if ($request->hasFile('icon')) {
            $data['icon_path'] = $request->file('icon')->store('area_icons', 's3');
        }

        Area::create($data);

        return redirect()->route('admin.areas.index')->with('success', 'Área creada exitosamente.');
    }

    /**
     * @param  \App\Models\Area  $area
     * @return \Illuminate\View\View
     */
    public function edit(Area $area)
    {
        return view('admin.areas.edit', compact('area'));
    }

    /**
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
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:50048',
            'emitter_name' => 'nullable|string|max:255',
            'emitter_phone' => 'nullable|string|max:20',
            'emitter_address' => 'nullable|string|max:255',
            'emitter_colonia' => 'nullable|string|max:255',
            'emitter_cp' => 'nullable|string|max:10',
            'is_client' => 'boolean',            
        ]);

        $data = $request->all();

        $data['is_client'] = $request->has('is_client');

        if ($request->hasFile('icon')) {
            if ($area->icon_path && Storage::disk('s3')->exists($area->icon_path)) {
                Storage::disk('s3')->delete($area->icon_path);
            }
            $data['icon_path'] = $request->file('icon')->store('area_icons', 's3');
        } elseif ($request->input('remove_icon')) {
            if ($area->icon_path && Storage::disk('s3')->exists($area->icon_path)) {
                Storage::disk('s3')->delete($area->icon_path);
            }
            $data['icon_path'] = null;
        }

        $area->update($data);

        return redirect()->route('admin.areas.index')->with('success', 'Área actualizada exitosamente.');
    }

    /**
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Area $area)
    {
        $area->folders->each(function ($folder) {
            $folder->delete();
        });

        if ($area->icon_path && Storage::disk('s3')->exists($area->icon_path)) {
            Storage::disk('s3')->delete($area->icon_path);
        }

        $area->delete();

        return redirect()->route('admin.areas.index')->with('success', 'Área eliminada exitosamente.');
    }
}