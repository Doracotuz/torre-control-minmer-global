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
        // Cargar todas las relaciones necesarias para el PDF
        $assignment->load('member.position', 'asset.model.category', 'asset.model.manufacturer', 'asset.softwareAssignments.license');

        // Convertir la imagen del logo a base64 para incrustarla de forma segura en el PDF
        $logoPath = 'LogoAzul.png'; // Asegúrate que esta es la ruta en tu disco s3
        $logoBase64 = null;
        if (Storage::disk('s3')->exists($logoPath)) {
            $logoContent = Storage::disk('s3')->get($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }

        $data = [
            'assignment' => $assignment,
            'logoBase64' => $logoBase64,
        ];

        // Cargar la vista y pasarle los datos
        $pdf = PDF::loadView('asset-management.pdfs.assignment', $data);

        // Generar un nombre de archivo dinámico
        $fileName = 'Responsiva-' . $assignment->asset->asset_tag . '-' . $assignment->member->name . '.pdf';

        // Mostrar el PDF en el navegador para previsualizarlo y descargarlo
        return $pdf->stream($fileName);
    }
}