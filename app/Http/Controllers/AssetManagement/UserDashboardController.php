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
    public function index(Request $request)
    {
        $query = OrganigramMember::whereHas('assignments', function ($query) {
                $query->whereNull('actual_return_date');
            })
            ->withCount(['assignments' => function ($query) {
                $query->whereNull('actual_return_date');
            }]);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('position', function ($posQuery) use ($searchTerm) {
                      $posQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

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
                      $q->whereIn('name', ['Monitor', 'Pantalla']);
                  });
        }]);

        $members = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('asset-management.user-dashboard.index', compact('members'));
    }

    public function show(OrganigramMember $member)
    {
        $currentAssignments = $member->assignments()
            ->whereNull('actual_return_date')
            ->with(['asset.model.category', 'asset.model.manufacturer'])
            ->get();

        $assignmentHistory = $member->assignments()
            ->whereNotNull('actual_return_date')
            ->with(['asset.model.category', 'asset.model.manufacturer'])
            ->orderBy('actual_return_date', 'desc')
            ->get();

        $responsivas = $member->userResponsivas; 

        return view('asset-management.user-dashboard.show', compact(
            'member', 
            'currentAssignments', 
            'assignmentHistory',  
            'responsivas'
        ));
    }

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

        $data = [
            'member' => $member,
            'assignments' => $assignments,
            'logoBase64' => $logoBase64,
            'documentId' => $documentId,
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