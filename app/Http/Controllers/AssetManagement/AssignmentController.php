<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareAsset;
use App\Models\Assignment;
use App\Models\OrganigramMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    /**
     * Muestra el formulario para asignar un activo específico.
     */
    public function create(HardwareAsset $asset)
    {
        if ($asset->status !== 'En Almacén') {
            return redirect()->route('asset-management.assets.show', $asset)
                ->with('error', 'Este activo no está disponible para ser asignado.');
        }

        $members = OrganigramMember::orderBy('name')->get();
        return view('asset-management.assignments.create', compact('asset', 'members'));
    }

    /**
     * Guarda la nueva asignación en la base de datos.
     */
    public function store(Request $request, HardwareAsset $asset)
    {
        $request->validate([
            'organigram_member_id' => 'required|exists:organigram_members,id',
            'assignment_date' => 'required|date',
        ]);

        if ($asset->status !== 'En Almacén') {
            return back()->with('error', 'Este activo ya no está disponible.');
        }

        DB::transaction(function () use ($request, $asset) {
            // 1. Crear el registro de asignación
            Assignment::create([
                'hardware_asset_id' => $asset->id,
                'organigram_member_id' => $request->organigram_member_id,
                'assignment_date' => $request->assignment_date,
            ]);

            // 2. Actualizar el estado del activo
            $asset->status = 'Asignado';
            $asset->save();
        });

        return redirect()->route('asset-management.assets.show', $asset)
            ->with('success', 'Activo asignado exitosamente.');
    }

    /**
     * Registra la devolución de un activo.
     */
    public function return(Assignment $assignment)
    {
        if ($assignment->actual_return_date) {
            return back()->with('error', 'Esta asignación ya ha sido marcada como devuelta.');
        }

        DB::transaction(function () use ($assignment) {
            // 1. Marcar la fecha de devolución en la asignación
            $assignment->actual_return_date = now();
            $assignment->save();

            // 2. Actualizar el estado del activo a "En Almacén"
            $asset = $assignment->asset;
            $asset->status = 'En Almacén';
            $asset->save();
        });

        return redirect()->route('asset-management.assets.show', $assignment->asset)
            ->with('success', 'Devolución registrada exitosamente. El activo está disponible en almacén.');
    }
}