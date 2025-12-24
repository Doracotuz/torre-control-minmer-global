<?php

namespace App\Http\Controllers;

use App\Models\ElectronicLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ElectronicLabelController extends Controller
{
    public function index()
    {
        return view('electronic_label.index');
    }

    public function create()
    {
        return view('electronic_label.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:5000',
            'series' => 'required|string|size:2|alpha_num',
            'label_type' => 'required|string|max:255',
            'elaboration_date' => 'required|date',
            'label_batch' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'alcohol_content' => 'required|numeric|between:0,100',
            'capacity' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'packaging_date' => 'required|date',
            'product_batch' => 'required|string|max:255',
            'maker_name' => 'required|string|max:255',
            'maker_rfc' => 'required|string|max:13',
        ]);

        $series = $request->series;
        $quantity = $request->quantity;
        $createdCount = 0;

        DB::transaction(function () use ($request, $series, $quantity, &$createdCount) {
            
            $lastLabel = ElectronicLabel::where('series', $series)
                                        ->lockForUpdate()
                                        ->orderBy('consecutive', 'desc')
                                        ->first();

            $currentConsecutive = $lastLabel ? $lastLabel->consecutive : 0;

            for ($i = 1; $i <= $quantity; $i++) {
                $currentConsecutive++;

                $folio = $series . '-' . str_pad($currentConsecutive, 10, '0', STR_PAD_LEFT);
                $uniqueId = Str::random(52);
                $fullUrl = url('/app/qr/faces/pages/mobile/validadorqr/' . $uniqueId);

                ElectronicLabel::create([
                    'series' => $series,
                    'consecutive' => $currentConsecutive,
                    'folio' => $folio,
                    'unique_identifier' => $uniqueId,
                    'full_url' => $fullUrl,
                    'user_id' => Auth::id(),

                    'label_type' => $request->label_type,
                    'elaboration_date' => $request->elaboration_date,
                    'label_batch' => $request->label_batch,

                    'product_name' => $request->product_name,
                    'product_type' => $request->product_type,
                    'alcohol_content' => $request->alcohol_content,
                    'capacity' => $request->capacity,
                    'origin' => $request->origin,
                    'packaging_date' => $request->packaging_date,
                    'product_batch' => $request->product_batch,

                    'maker_name' => $request->maker_name,
                    'maker_rfc' => $request->maker_rfc,
                ]);

                $createdCount++;
            }
        });

        return redirect()->route('electronic-label.create')
                         ->with('success', "¡Éxito! Se han generado {$createdCount} marbetes de la serie '{$series}' con la información registrada.");
    }

    public function records()   
    {
        $batches = ElectronicLabel::selectRaw('
                series, 
                count(*) as total, 
                min(consecutive) as start_folio, 
                max(consecutive) as end_folio, 
                product_name, 
                label_type,
                created_at
            ')
            ->groupBy('created_at', 'series', 'product_name', 'label_type')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('electronic_label.records', compact('batches'));
    }

    public function downloadCsv($series, $date)
    {
        $labels = ElectronicLabel::where('series', $series)
            ->where('created_at', $date)
            ->get();

        if ($labels->isEmpty()) {
            return back()->with('error', 'No se encontraron registros para exportar.');
        }

        $fileName = 'Marbetes_' . $series . '_' . date('Ymd_His', strtotime($date)) . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($labels) {
            $file = fopen('php://output', 'w');
            
            fputs($file, "\xEF\xBB\xBF"); 

            fputcsv($file, [
                'Folio Completo', 
                'Link QR', 
                'Identificador Único',
                'Serie', 
                'Consecutivo', 
                'Producto', 
                'Tipo Producto',
                'Lote Producto',
                'Fecha Envasado',
                'Fabricante',
                'RFC Fabricante',
                'Fecha Creación'
            ]);

            foreach ($labels as $row) {
                fputcsv($file, [
                    $row->folio,
                    $row->full_url,
                    $row->unique_identifier,
                    $row->series,
                    $row->consecutive,
                    $row->product_name,
                    $row->product_type,
                    $row->product_batch,
                    $row->packaging_date->format('d/m/Y'),
                    $row->maker_name,
                    $row->maker_rfc,
                    $row->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function validateQr($unique_identifier)
    {
        $label = ElectronicLabel::where('unique_identifier', $unique_identifier)->firstOrFail();

        return view('electronic_label.public_validation', compact('label'));
    }    

    public function showBatch($series, $date)
    {
        $labels = ElectronicLabel::where('series', $series)
            ->where('created_at', $date)
            ->paginate(50);

        if ($labels->isEmpty()) {
            return redirect()->route('electronic-label.records')->with('error', 'Lote no encontrado.');
        }

        $batchInfo = $labels->first();

        return view('electronic_label.show', compact('labels', 'batchInfo', 'date'));
    }

    public function destroyBatch($series, $date)
    {
        $deleted = ElectronicLabel::where('series', $series)
            ->where('created_at', $date)
            ->delete();

        return redirect()->route('electronic-label.records')
            ->with('success', "Se eliminaron correctamente {$deleted} marbetes del lote Serie {$series}.");
    }

}