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
    public function index(Request $request) // <-- Añadir Request $request
    {
        // Consulta base
        $query = OrganigramMember::whereHas('assignments', function ($query) {
                $query->whereNull('actual_return_date');
            })
            ->withCount(['assignments' => function ($query) {
                $query->whereNull('actual_return_date');
            }]);

        // --- INICIO DE MODIFICACIÓN (PARA BÚSQUEDA Y CONTEO DE ICONOS) ---

        // Lógica de Búsqueda
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('position', function ($posQuery) use ($searchTerm) {
                      $posQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Conteo de iconos para la vista de cuadrícula
        $query->withCount(['assignments as laptop_count' => function ($query) {
            $query->whereNull('actual_return_date')
                  ->whereHas('asset.model.category', function ($q) {
                      $q->where('name', 'Laptop');
                  });
        }]);
        
        $query->withCount(['assignments as phone_count' => function ($query) {
            $query->whereNull('actual_return_date')
                  ->whereHas('asset.model.category', function ($q) {
                      $q->where('name', 'Celular');
                  });
        }]);

        $query->withCount(['assignments as monitor_count' => function ($query) {
            $query->whereNull('actual_return_date')
                  ->whereHas('asset.model.category', function ($q) {
                      $q->whereIn('name', ['Monitor', 'Pantalla']); // Puedes ajustar estos nombres
                  });
        }]);
        
        // --- FIN DE MODIFICACIÓN ---

        $members = $query->orderBy('name')->paginate(12)->withQueryString(); // 12 es un buen número para grids de 3

        return view('asset-management.user-dashboard.index', compact('members'));
    }

    /**
     * Muestra los detalles y la lista de activos de un usuario específico.
     */
    public function show(OrganigramMember $member)
    {
        // 1. Obtenemos los activos ASIGNADOS AHORA (para la pestaña 1)
        $currentAssignments = $member->assignments()
            ->whereNull('actual_return_date')
            ->with(['asset.model.category', 'asset.model.manufacturer'])
            ->get();

        // 2. Obtenemos el HISTORIAL de asignaciones (para la pestaña 2)
        $assignmentHistory = $member->assignments()
            ->whereNotNull('actual_return_date') // La diferencia clave
            ->with(['asset.model.category', 'asset.model.manufacturer'])
            ->orderBy('actual_return_date', 'desc') // Ordenar por más reciente
            ->get();

        // 3. Obtenemos las RESPONSIVAS CONSOLIDADAS (para la pestaña 3)
        $responsivas = $member->userResponsivas; 

        // 4. Pasamos TODAS las variables a la vista
        return view('asset-management.user-dashboard.show', compact(
            'member', 
            'currentAssignments', 
            'assignmentHistory',  
            'responsivas'
        ));
    }

    /**
     * Genera el PDF de responsiva consolidada para un usuario.
     */
    public function generateConsolidatedPdf(OrganigramMember $member)
    {
        $assignments = $member->assignments()->whereNull('actual_return_date')->with(['asset.model.category', 'asset.model.manufacturer', 'asset.softwareAssignments.license'])->get();
        
        $logoPath = 'LogoAzul.png';
        $logoBase64 = null;
        if (Storage::disk('s3')->exists($logoPath)) {
            $logoContent = Storage::disk('s3')->get($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }
        
        $documentId = 'RESP-CONSOLIDADA-' . $member->id . '-' . date('Ymd');

        // --- SE ELIMINÓ TODA LA LÓGICA DEL CÓDIGO QR DE AQUÍ ---

        $data = [
            'member' => $member,
            'assignments' => $assignments,
            'logoBase64' => $logoBase64,
            'documentId' => $documentId,
            // Se eliminó la variable 'qrCode'
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