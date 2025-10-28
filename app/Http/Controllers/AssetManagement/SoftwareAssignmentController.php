<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareAsset;
use App\Models\SoftwareLicense;
use App\Models\SoftwareAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SoftwareAssignmentController extends Controller
{
    public function create(HardwareAsset $asset)
    {
        $availableLicenses = SoftwareLicense::whereRaw('(SELECT COUNT(*) FROM software_assignments WHERE software_license_id = software_licenses.id) < total_seats')
            ->orderBy('name')
            ->get();

        return view('asset-management.software-assignments.create', compact('asset', 'availableLicenses'));
    }

    public function store(Request $request, HardwareAsset $asset)
    {
        $request->validate([
            'software_license_id' => 'required|exists:software_licenses,id',
            'install_date' => 'required|date',
        ]);

        $license = SoftwareLicense::findOrFail($request->software_license_id);

        DB::transaction(function () use ($license, $asset, $request) {
            if ($license->used_seats >= $license->total_seats) {
                return back()->with('error', 'No hay asientos disponibles para esta licencia.');
            }

            SoftwareAssignment::create([
                'hardware_asset_id' => $asset->id,
                'software_license_id' => $license->id,
                'install_date' => $request->install_date,
            ]);
        });

        return redirect()->route('asset-management.assets.show', $asset)
            ->with('success', 'Software asignado al activo exitosamente.');
    }

    public function destroy(SoftwareAssignment $assignment)
    {
        $asset = $assignment->asset;
        $assignment->delete();

        return redirect()->route('asset-management.assets.show', $asset)
            ->with('success', 'La asignación de software ha sido eliminada.');
    }
}