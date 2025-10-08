<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\OrganigramMember;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\UserResponsiva;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    /**
     * Muestra una lista de usuarios que tienen activos asignados actualmente.
     */
    public function index()
    {
        $members = OrganigramMember::whereHas('assignments', function ($query) {
                $query->whereNull('actual_return_date');
            })
            ->withCount(['assignments' => function ($query) {
                $query->whereNull('actual_return_date');
            }])
            ->orderBy('name')
            ->paginate(20);

        return view('asset-management.user-dashboard.index', compact('members'));
    }

    /**
     * Muestra los detalles y la lista de activos de un usuario específico.
     */
    public function show(OrganigramMember $member)
    {
        $assignments = $member->assignments()
            ->whereNull('actual_return_date')
            ->with(['asset.model.category', 'asset.model.manufacturer'])
            ->get();

        // <-- AÑADIR ESTA LÍNEA -->
        $responsivas = $member->userResponsivas; 

        return view('asset-management.user-dashboard.show', compact('member', 'assignments', 'responsivas')); // <-- MODIFICAR
    }

    /**
     * Genera el PDF de responsiva consolidada para un usuario.
     */
    public function generateConsolidatedPdf(OrganigramMember $member)
    {
        $assignments = $member->assignments()
            ->whereNull('actual_return_date')
            ->with(['asset.model.category', 'asset.model.manufacturer', 'asset.softwareAssignments.license'])
            ->get();
            
        $logoPath = 'LogoAzul.png';
        $logoBase64 = null;
        if (Storage::disk('s3')->exists($logoPath)) {
            $logoContent = Storage::disk('s3')->get($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }

        $data = [
            'member' => $member,
            'assignments' => $assignments,
            'logoBase64' => $logoBase64,
        ];

        $pdf = PDF::loadView('asset-management.pdfs.consolidated-assignment', $data);
        $fileName = 'Responsiva-Consolidada-' . $member->name . '.pdf';

        return $pdf->stream($fileName);
    }

    public function uploadConsolidatedReceipt(Request $request, OrganigramMember $member)
    {
        $request->validate([
            'signed_receipt' => 'required|file|mimes:pdf|max:5120',
        ]);

        $path = $request->file('signed_receipt')->store('assets/consolidated-receipts', 's3');

        UserResponsiva::create([
            'organigram_member_id' => $member->id,
            'file_path' => $path,
            'generated_date' => now(),
        ]);

        return back()->with('success', 'Responsiva consolidada subida exitosamente.');
    }
    
}