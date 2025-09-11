<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsOrder;
use App\Models\CsOrderDetail;
use App\Models\CsOrderDetailUpc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Guia;

class ValidationController extends Controller
{
    public function index(Request $request)
    {
        // La consulta base ahora incluye la relación a la guía para optimización
        $query = CsOrder::with('details.product', 'details.upc', 'plannings.guia');

        // Filtro de búsqueda general por SO, Cliente o Guía
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('so_number', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('plannings.guia', function ($guiaQuery) use ($searchTerm) {
                      $guiaQuery->where('guia', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        // Filtro unificado de estatus que busca en tres columnas diferentes
        if ($request->filled('status') && is_array($request->status)) {
            $selectedStatuses = $request->status;
            
            $query->where(function ($q) use ($selectedStatuses) {
                // 1. Busca en el estatus principal de la orden (`cs_orders.status`)
                $q->whereIn('status', $selectedStatuses);

                // 2. O busca en el estatus de auditoría de la orden (`cs_orders.audit_status`)
                $q->orWhereIn('audit_status', $selectedStatuses);

                // 3. O busca en el estatus de la guía asociada a la orden (`guias.estatus`)
                $q->orWhereHas('plannings.guia', function ($guiaQuery) use ($selectedStatuses) {
                    $guiaQuery->whereIn('estatus', $selectedStatuses);
                });
            });
        }

        // Otros filtros existentes
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', $request->customer_name);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('creation_date', [$request->start_date, $request->end_date]);
        }

        // Paginación de resultados
        $orders = $query->orderBy('creation_date', 'desc')->paginate(10);
        
        // Recolección de todos los estatus únicos de las tres fuentes para los checkboxes del filtro
        $orderStatuses = CsOrder::whereNotNull('status')->distinct()->pluck('status');
        $auditStatuses = CsOrder::whereNotNull('audit_status')->distinct()->pluck('audit_status');
        $guiaStatuses = Guia::whereNotNull('estatus')->distinct()->pluck('estatus');
        
        // Se unen las tres colecciones, se eliminan duplicados y se ordenan alfabéticamente
        $allStatuses = $orderStatuses->merge($auditStatuses)->merge($guiaStatuses)->unique()->sort()->values();

        // Recolección de datos para otros filtros
        $baseQueryForFilters = CsOrder::query();
        $customers = (clone $baseQueryForFilters)->distinct()->orderBy('customer_name')->pluck('customer_name');
        $channels = (clone $baseQueryForFilters)->distinct()->orderBy('channel')->pluck('channel');

        // Se envían todas las variables a la vista
        return view('customer-service.validation.index', compact('orders', 'customers', 'channels', 'allStatuses'));
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