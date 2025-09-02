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
        $query = CsOrder::whereNotIn('status', ['Terminado', 'Cancelado'])
                        ->with('details.product', 'details.upc');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('so_number', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_name', 'like', "%{$searchTerm}%");
            });
        }
        
        // --- CORRECCIÓN: Se añade el filtro por estatus ---
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // --- CORRECCIÓN: Se añade el filtro por canal ---
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('customer_name')) {
            $query->where('customer_name', $request->customer_name);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('creation_date', [$request->start_date, $request->end_date]);
        }

        $orders = $query->paginate(10);
        
        // --- CORRECCIÓN: Se obtienen los datos para los nuevos filtros ---
        $baseQuery = CsOrder::whereNotIn('status', ['Terminado', 'Cancelado']);
        $customers = (clone $baseQuery)->distinct()->orderBy('customer_name')->pluck('customer_name');
        $channels = (clone $baseQuery)->distinct()->orderBy('channel')->pluck('channel');
        $statuses = (clone $baseQuery)->distinct()->orderBy('status')->pluck('status');

        return view('customer-service.validation.index', compact('orders', 'customers', 'channels', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upcs' => 'required|array',
            'upcs.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['upcs'] as $detailId => $upcValue) {
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

        return back()->with('success', 'UPCs guardados exitosamente.');
    }

    /**
     * CORRECCIÓN: Ahora genera la plantilla basada en los IDs de las órdenes seleccionadas.
     * Si no se seleccionan IDs, genera una plantilla vacía.
     */
    public function downloadTemplate(Request $request)
    {
        $orderIds = $request->query('ids');
        $orders = collect(); // Inicia una colección vacía por defecto

        // Si se proporcionaron IDs, busca esas órdenes
        if (!empty($orderIds) && is_array($orderIds)) {
            $orders = CsOrder::whereIn('id', $orderIds)->with('details')->get();
        }

        $fileName = "plantilla_upc_seleccion.csv";
        $headers = ["Content-type" => "text/csv; charset=UTF-8", "Content-Disposition" => "attachment; filename=$fileName"];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['so_number', 'sku', 'upc_a_llenar']);

            // Si hay órdenes seleccionadas, itera sobre ellas y sus detalles
            if ($orders->isNotEmpty()) {
                foreach ($orders as $order) {
                    foreach ($order->details as $detail) {
                        fputcsv($file, [
                            $order->so_number,
                            $detail->sku,
                            '' // Columna vacía para llenar el UPC
                        ]);
                    }
                }
            }
            
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * La lógica de importación ya es compatible con el nuevo formato, no requiere cambios.
     */
    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');
        fgetcsv($file); 

        DB::transaction(function () use ($file) {
            while (($row = fgetcsv($file)) !== FALSE) {
                $soNumber = trim($row[0]);
                $sku = trim($row[1]);
                $upcValue = trim($row[2]);

                if (empty($soNumber) || empty($sku)) {
                    continue;
                }

                // CORRECCIÓN: Busca TODOS los detalles que coincidan con SO + SKU
                $details = CsOrderDetail::where('sku', $sku)
                    ->whereHas('order', function ($query) use ($soNumber) {
                        $query->where('so_number', $soNumber);
                    })
                    ->get(); // Usamos get() en lugar de first()

                // Si encuentra uno o más detalles, los recorre y actualiza
                if ($details->isNotEmpty()) {
                    foreach ($details as $detail) {
                        $upc = $upcValue ?: $detail->sku;
                        CsOrderDetailUpc::updateOrCreate(
                            ['cs_order_detail_id' => $detail->id],
                            ['upc' => $upc]
                        );
                    }
                }
            }
        });
        fclose($file);

        return redirect()->route('customer-service.validation.index')->with('success', 'Archivo de UPCs importado exitosamente.');
    }
}