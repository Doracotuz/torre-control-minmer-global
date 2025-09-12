<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditReportController extends Controller
{
    /**
     * Muestra la lista de guías cuyas auditorías han sido completadas.
     */
    public function index(Request $request)
    {
        $query = Guia::query()
            // La nueva regla para una guía completada:
            // 1. Debe TENER al menos una auditoría asociada.
            ->whereHas('plannings.order.audits')
            // 2. Y NO debe tener NINGUNA auditoría que NO esté en estatus 'Finalizada'.
            ->whereDoesntHave('plannings.order.audits', function ($q) {
                $q->where('status', '!=', 'Finalizada');
            })
            ->with('plannings.order.events.user'); // Cargamos relaciones para obtener datos en la vista

        // Lógica de búsqueda
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('guia', 'like', $searchTerm)
                  ->orWhereHas('plannings.order', fn($oq) => $oq->where('so_number', 'like', $searchTerm))
                  ->orWhereHas('plannings.order', fn($oq) => $oq->where('customer_name', 'like', $searchTerm));
            });
        }

        $guias = $query->orderBy('updated_at', 'desc')->paginate(20);

        return view('customer-service.reports.index', compact('guias'));
    }

    /**
     * Muestra la vista de detalle de una auditoría completada para una guía.
     */
    public function show(Guia $guia)
    {
        // Cargamos todas las relaciones necesarias para mostrar el reporte completo,
        // incluyendo los registros de auditoría y sus datos.
        $guia->load([
            'plannings.order.details.product',
            'plannings.order.customer',
            'plannings.order.audits', // ¡Crucial! Carga los registros de auditoría
            'incidencias',
            'plannings.order.events.user'
        ]);
        
        return view('customer-service.reports.show', compact('guia'));
    }

    /**
     * Genera un reporte en PDF para una guía auditada.
     */
    public function generatePdf(Guia $guia)
    {
        // Cargamos las mismas relaciones que en el método show.
        $guia->load([
            'plannings.order.details.product',
            'plannings.order.customer',
            'plannings.order.audits',
            'incidencias',
            'plannings.order.events.user'
        ]);

        $logoUrl = Storage::disk('s3')->url('LogoAzul.png');

        $pdf = Pdf::loadView('customer-service.reports.pdf', compact('guia', 'logoUrl'));
        
        // Usamos stream() para que se muestre en el navegador en lugar de descargar automáticamente.
        return $pdf->stream('reporte_auditoria_' . $guia->guia . '.pdf');
    }
}