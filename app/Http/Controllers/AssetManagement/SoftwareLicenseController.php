<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\SoftwareLicense;
use Illuminate\Http\Request;

class SoftwareLicenseController extends Controller
{
    public function index()
    {
        $licenses = SoftwareLicense::withCount('assignments')->orderBy('name')->paginate(15);
        return view('asset-management.software-licenses.index', compact('licenses'));
    }

    public function create()
    {
        return view('asset-management.software-licenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_seats' => 'required|integer|min:1',
            'license_key' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:purchase_date',
        ]);

        SoftwareLicense::create($validated);
        return redirect()->route('asset-management.software-licenses.index')->with('success', 'Licencia de software creada exitosamente.');
    }

    public function edit(SoftwareLicense $softwareLicense)
    {
        return view('asset-management.software-licenses.edit', compact('softwareLicense'));
    }

    public function update(Request $request, SoftwareLicense $softwareLicense)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_seats' => 'required|integer|min:1',
            'license_key' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:purchase_date',
        ]);

        $softwareLicense->update($validated);
        return redirect()->route('asset-management.software-licenses.index')->with('success', 'Licencia actualizada exitosamente.');
    }

    public function destroy(SoftwareLicense $softwareLicense)
    {
        if ($softwareLicense->assignments()->exists()) {
            return back()->with('error', 'No se puede eliminar una licencia que tiene asignaciones activas.');
        }

        $softwareLicense->delete();
        return redirect()->route('asset-management.software-licenses.index')->with('success', 'Licencia eliminada exitosamente.');
    }
}