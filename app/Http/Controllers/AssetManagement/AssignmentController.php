<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareAsset;
use App\Models\Assignment;
use App\Models\OrganigramMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            $assignment = Assignment::create([
                'hardware_asset_id' => $asset->id,
                'organigram_member_id' => $request->organigram_member_id,
                'assignment_date' => $request->assignment_date,
            ]);

            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Asignación', // O 'Préstamo' en storeLoan
                'notes' => 'Asignado a ' . $assignment->member->name,
                'loggable_id' => $assignment->id,
                'loggable_type' => Assignment::class,
            ]);

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

            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Devolución',
                'notes' => 'Devuelto por ' . $assignment->member->name . '. Disponible en almacén.'
            ]);

            $asset->status = 'En Almacén';
            $asset->save();
        });

        return redirect()->route('asset-management.assets.show', $assignment->asset)
            ->with('success', 'Devolución registrada exitosamente. El activo está disponible en almacén.');
    }

    public function createLoan(HardwareAsset $asset)
    {
        if ($asset->status !== 'En Almacén') {
            return redirect()->route('asset-management.assets.show', $asset)
                ->with('error', 'Este activo no está disponible para ser prestado.');
        }

        $members = OrganigramMember::orderBy('name')->get();
        return view('asset-management.assignments.create-loan', compact('asset', 'members'));
    }

    /**
     * Guarda el nuevo préstamo en la base de datos.
     */
    public function storeLoan(Request $request, HardwareAsset $asset)
    {
        $validated = $request->validate([
            'organigram_member_id' => 'required|exists:organigram_members,id',
            'assignment_date' => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:assignment_date',
        ]);

        if ($asset->status !== 'En Almacén') {
            return back()->with('error', 'Este activo ya no está disponible.');
        }

        DB::transaction(function () use ($validated, $asset) {
            $member = \App\Models\OrganigramMember::find($validated['organigram_member_id']);

            // 1. Crear el registro del préstamo
            $assignment = $asset->assignments()->create([
                'type' => 'Préstamo',
                'organigram_member_id' => $validated['organigram_member_id'],
                'assignment_date' => $validated['assignment_date'],
                'expected_return_date' => $validated['expected_return_date'],
            ]);

            // 2. REGISTRAR EL EVENTO EN EL LOG
            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Préstamo',
                'notes' => 'Prestado a ' . $member->name . ' hasta el ' . \Carbon\Carbon::parse($validated['expected_return_date'])->format('d/m/Y'),
                'loggable_id' => $assignment->id,
                'loggable_type' => \App\Models\Assignment::class,
            ]);

            // 3. Actualizar el estatus del activo
            $asset->status = 'Prestado';
            $asset->save();
        });

        return redirect()->route('asset-management.assets.show', $asset)
            ->with('success', 'Activo prestado exitosamente.');
    }

    public function uploadReceipt(Request $request, Assignment $assignment)
    {
        $request->validate([
            'signed_receipt' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Eliminar el archivo antiguo si existe
        if ($assignment->signed_receipt_path) {
            Storage::disk('s3')->delete($assignment->signed_receipt_path);
        }

        $path = $request->file('signed_receipt')->store('assets/receipts', 's3');
        $assignment->update(['signed_receipt_path' => $path]);

        return back()->with('success', 'Responsiva firmada subida exitosamente.');
    }    


}