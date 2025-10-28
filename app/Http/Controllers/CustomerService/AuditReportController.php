<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Guia::query()
            ->whereHas('plannings.order.audits')
            ->whereDoesntHave('plannings.order.audits', function ($q) {
                $q->where('status', '!=', 'Finalizada');
            })
            ->with('plannings.order.events.user');

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

    public function show(Guia $guia)
    {
        $guia->load([
            'plannings.order.details.product',
            'plannings.order.customer',
            'plannings.order.audits',
            'incidencias',
            'plannings.order.events.user'
        ]);
        
        return view('customer-service.reports.show', compact('guia'));
    }

    public function generatePdf(Guia $guia)
    {
        $guia->load([
            'plannings.order.details.product',
            'plannings.order.customer',
            'plannings.order.audits',
            'incidencias',
            'plannings.order.events.user'
        ]);

        $logoUrl = Storage::disk('s3')->url('LogoAzul.png');

        $pdf = Pdf::loadView('customer-service.reports.pdf', compact('guia', 'logoUrl'));
        
        return $pdf->stream('reporte_auditoria_' . $guia->guia . '.pdf');
    }
}