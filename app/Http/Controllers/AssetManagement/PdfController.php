<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function generateAssignmentPdf(Assignment $assignment)
    {
        $assignment->load('member.position', 'asset.model.category', 'asset.model.manufacturer', 'asset.softwareAssignments.license');

        $logoPath = 'LogoAzul.png';
        $logoBase64 = null;
        if (Storage::disk('s3')->exists($logoPath)) {
            $logoContent = Storage::disk('s3')->get($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }

        $data = [
            'assignment' => $assignment,
            'logoBase64' => $logoBase64,
        ];

        $pdf = PDF::loadView('asset-management.pdfs.assignment', $data);

        $fileName = 'Responsiva-' . $assignment->asset->asset_tag . '-' . $assignment->member->name . '.pdf';

        return $pdf->stream($fileName);
    }
}