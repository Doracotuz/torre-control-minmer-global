<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsOrder;
use App\Models\CsOrderDetail;
use App\Models\CsOrderDetailUpc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValidationController extends Controller
{
    public function index(Request $request)
    {
        // --- INICIA LÓGICA CORREGIDA ---
        // La consulta ahora busca todas las órdenes que no estén terminadas o canceladas.
        $query = CsOrder::whereNotIn('status', ['Terminado', 'Cancelado'])
                        ->with('details.product', 'details.upc');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('so_number', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_name', 'like', "%{$searchTerm}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_name')) {
            $query->where('customer_name', $request->customer_name);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('creation_date', [$request->start_date, $request->end_date]);
        }

        $orders = $query->paginate(10);
        $customers = CsOrder::whereNotIn('status', ['Terminado', 'Cancelado'])->distinct()->pluck('customer_name');
        // --- TERMINA LÓGICA CORREGIDA ---

        return view('customer-service.validation.index', compact('orders', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upcs' => 'required|array',
            'upcs.*' => 'required|string',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['upcs'] as $detailId => $upcValue) {
                $detail = CsOrderDetail::find($detailId);
                if ($detail) {
                    $upc = $upcValue ?: $detail->sku; // Si está vacío, usa el SKU
                    CsOrderDetailUpc::updateOrCreate(
                        ['cs_order_detail_id' => $detailId],
                        ['upc' => $upc]
                    );
                }
            }
        });

        return back()->with('success', 'UPCs guardados exitosamente.');
    }

    public function downloadTemplate()
    {
        $orders = CsOrder::where('status', 'Pendiente')->with('details.product')->get();
        $fileName = "plantilla_upc.csv";
        $headers = ["Content-type" => "text/csv; charset=UTF-8", "Content-Disposition" => "attachment; filename=$fileName"];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['order_detail_id', 'so_number', 'sku', 'description', 'upc_a_llenar']);

            foreach ($orders as $order) {
                foreach ($order->details as $detail) {
                    fputcsv($file, [
                        $detail->id,
                        $order->so_number,
                        $detail->sku,
                        $detail->product->description ?? '',
                        '' // Columna vacía para llenar
                    ]);
                }
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');
        fgetcsv($file); // Omitir cabecera

        DB::transaction(function () use ($file) {
            while (($row = fgetcsv($file)) !== FALSE) {
                $detailId = $row[0];
                $upcValue = $row[4];
                $detail = CsOrderDetail::find($detailId);
                if ($detail) {
                    $upc = $upcValue ?: $detail->sku;
                    CsOrderDetailUpc::updateOrCreate(
                        ['cs_order_detail_id' => $detailId],
                        ['upc' => $upc]
                    );
                }
            }
        });
        fclose($file);

        return redirect()->route('customer-service.validation.index')->with('success', 'Archivo de UPCs importado exitosamente.');
    }
}
