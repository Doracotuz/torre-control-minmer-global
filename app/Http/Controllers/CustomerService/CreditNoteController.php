<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CsCreditNote;
use App\Models\CsCreditNoteDetail;
use App\Models\CsOrder;
use App\Models\CsWarehouse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use App\Models\CsOrderEvent;
use Illuminate\Support\Facades\Auth;

class CreditNoteController extends Controller
{
    /**
     * Muestra una lista de notas de crédito con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $query = CsCreditNote::with('order', 'createdBy');

        // Aplicar filtro de búsqueda general
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('credit_note', 'like', "%{$search}%")
                  ->orWhere('invoice', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($q_order) use ($search) {
                      $q_order->where('so_number', 'like', "%{$search}%");
                  });
            });
        }

        // Aplicar filtros específicos
        if ($request->has('start_date') && $request->start_date != '') {
            $query->where('capture_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->where('capture_date', '<=', $request->end_date);
        }
        if ($request->has('request_type') && $request->request_type != '') {
            $query->where('request_type', $request->request_type);
        }
        if ($request->has('warehouse_id') && $request->warehouse_id != '') {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->has('customer_name') && $request->customer_name != '') {
            $query->where('customer_name', 'like', "%{$request->customer_name}%");
        }

        $creditNotes = $query->latest()->paginate(10)->withQueryString();
        
        // Si es una solicitud AJAX, devuelve JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'data' => $creditNotes->items(),
                'links' => $creditNotes->linkCollection(),
                'current_page' => $creditNotes->currentPage(),
                'last_page' => $creditNotes->lastPage(),
            ]);
        }

        $warehouses = CsWarehouse::all();
        $requestTypes = ['Cancelación', 'Rechazo', 'Recolección', 'Devolución'];
        $customers = CsCreditNote::select('customer_name')->distinct()->orderBy('customer_name')->pluck('customer_name');
        
        return view('customer-service.credit-notes.index', compact('creditNotes', 'warehouses', 'requestTypes', 'customers'));
    }

    /**
     * Muestra el formulario para editar una nota de crédito.
     */
    public function edit(CsCreditNote $creditNote)
    {
        // Cargar los detalles de la nota de crédito y el producto asociado
        $creditNote->load('details.product');
        $warehouses = CsWarehouse::all();
        $requestTypes = ['Cancelación', 'Rechazo', 'Recolección', 'Devolución'];
        $causes = [
            "Administración de CS", "Administración de ejecutivo MHMX", "Calidad",
            "Incidencias IT", "Movimiento Virtual", "No entregado picking",
            "No entregado transporte", "Operación del cliente", "Siniestros"
        ];
        
        return view('customer-service.credit-notes.edit', compact('creditNote', 'warehouses', 'requestTypes', 'causes'));
    }

    /**
     * Actualiza la nota de crédito en la base de datos.
     */
    public function update(Request $request, CsCreditNote $creditNote)
    {
        $validatedData = $request->validate([
            'request_type' => 'required|string',
            'capture_date' => 'required|date',
            'credit_note' => 'nullable|string',
            'credit_note_date' => 'nullable|date',
            'customs_document' => 'required|string',
            'cause' => 'required|string',
            'cause_description' => 'required|string',
            'arrival_date' => 'nullable|date',
            'asn_close_date' => 'nullable|date',
            'asn' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);
        
        $originalData = $creditNote->getOriginal();

        $creditNote->update($validatedData);

        // Registro de cambios en la línea de tiempo
        $changes = collect($validatedData)->filter(function ($value, $key) use ($originalData) {
            return $originalData[$key] != $value;
        });

        if ($changes->isNotEmpty()) {
            $description = 'El usuario ' . Auth::user()->name . ' actualizó la Nota de Crédito. Se realizaron los siguientes cambios:';
            $fieldTranslations = [
                'request_type' => 'Tipo de Solicitud',
                'capture_date' => 'Fecha de Captura',
                'credit_note' => 'No. NC',
                'credit_note_date' => 'Fecha de NC',
                'customs_document' => 'Pedimento',
                'cause' => 'Causa',
                'cause_description' => 'Descripción de Causa',
                'arrival_date' => 'Fecha de Arribo',
                'asn_close_date' => 'Fecha de Cierre ASN',
                'asn' => 'ASN',
                'observations' => 'Observaciones',
            ];

            foreach ($changes as $key => $value) {
                $fieldName = $fieldTranslations[$key] ?? $key;
                $originalValue = $originalData[$key] ?? 'vacío';
                $description .= " El campo '{$fieldName}' se cambió de '{$originalValue}' a '{$value}'.";
            }
            
            // Crear el evento de actualización en la línea de tiempo del pedido
            CsOrderEvent::create([
                'cs_order_id' => $creditNote->cs_order_id,
                'user_id' => Auth::id(),
                'description' => $description
            ]);
        }
        
        // Lógica para actualizar los detalles del SKU (manteniendo tu implementación)
        if ($request->has('sku_details')) {
            foreach ($request->input('sku_details') as $detailId => $data) {
                $detail = CsCreditNoteDetail::find($detailId);
                if ($detail && $detail->quantity_returned != $data['quantity_returned']) {
                    $originalQuantity = $detail->quantity_returned;
                    $detail->update(['quantity_returned' => $data['quantity_returned']]);
                    
                    // Crear un evento por cada cambio en la cantidad de SKU
                    $skuDescription = 'El usuario ' . Auth::user()->name . ' cambió la cantidad devuelta para el SKU ' . $detail->sku . ' de ' . $originalQuantity . ' a ' . $data['quantity_returned'] . '.';
                    CsOrderEvent::create([
                        'cs_order_id' => $creditNote->cs_order_id,
                        'user_id' => Auth::id(),
                        'description' => $skuDescription
                    ]);
                }
            }
        }

        return redirect()->route('customer-service.credit-notes.index')->with('success', 'Nota de crédito actualizada exitosamente.');
    }

    /**
     * Elimina una nota de crédito.
     */
    public function destroy(CsCreditNote $creditNote)
    {
        $creditNote->delete();

        return redirect()->route('customer-service.credit-notes.index')->with('success', 'Nota de crédito eliminada exitosamente.');
    }

    /**
     * Exporta las notas de crédito a un archivo CSV.
     */
    public function exportCsv(Request $request)
    {
        $creditNotes = CsCreditNote::with(['order', 'details'])->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="notas_de_credito_detallado.csv"',
        ];

        $callback = function() use ($creditNotes) {
            $file = fopen('php://output', 'w');
            
            // Agrega la marca de orden de bytes (BOM) para compatibilidad con UTF-8 en Excel
            fputs($file, "\xEF\xBB\xBF");

            // Encabezados del CSV combinando ambas tablas
            fputcsv($file, [
                'ID Nota Credito', 'ID Orden', 'No. SO', 'Tipo Solicitud',
                'Fecha Captura', 'Cliente', 'Factura', 'ID Almacen',
                'Pedimento', 'Causa', 'Descripcion Causa', 'Fecha NC',
                'No. NC', 'Fecha Arribo', 'Fecha Cierre ASN', 'ASN',
                'Observaciones', 'ID Creador', 'Creado en', 'Actualizado en',
                'SKU Devuelto', 'Cantidad Devuelta',
            ]);

            foreach ($creditNotes as $note) {
                // Si la nota de crédito tiene detalles, crea una fila por cada uno.
                if ($note->details->count() > 0) {
                    foreach ($note->details as $detail) {
                        fputcsv($file, [
                            $note->id,
                            $note->cs_order_id,
                            $note->order->so_number ?? 'N/A',
                            $note->request_type,
                            $note->capture_date,
                            $note->customer_name,
                            $note->invoice,
                            $note->warehouse_id,
                            $note->customs_document,
                            $note->cause,
                            $note->cause_description,
                            $note->credit_note_date,
                            $note->credit_note,
                            $note->arrival_date,
                            $note->asn_close_date,
                            $note->asn,
                            $note->observations,
                            $note->created_by_user_id,
                            $note->created_at,
                            $note->updated_at,
                            $detail->sku,
                            $detail->quantity_returned,
                        ]);
                    }
                } else {
                    // Si no tiene detalles (caso poco común), exporta la fila principal
                    fputcsv($file, [
                        $note->id,
                        $note->cs_order_id,
                        $note->order->so_number ?? 'N/A',
                        $note->request_type,
                        $note->capture_date,
                        $note->customer_name,
                        $note->invoice,
                        $note->warehouse_id,
                        $note->customs_document,
                        $note->cause,
                        $note->cause_description,
                        $note->credit_note_date,
                        $note->credit_note,
                        $note->arrival_date,
                        $note->asn_close_date,
                        $note->asn,
                        $note->observations,
                        $note->created_by_user_id,
                        $note->created_at,
                        $note->updated_at,
                        'N/A',
                        'N/A',
                    ]);
                }
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Muestra el dashboard con los gráficos.
     */
    public function dashboard()
    {
        $requestTypes = CsCreditNote::selectRaw('request_type, count(*) as count')
                                    ->groupBy('request_type')
                                    ->pluck('count', 'request_type');
        
        $monthlyCounts = CsCreditNote::selectRaw('MONTH(capture_date) as month, count(*) as count')
                                     ->groupBy('month')
                                     ->orderBy('month')
                                     ->pluck('count', 'month');

        $causes = CsCreditNote::selectRaw('cause, count(*) as count')
                              ->groupBy('cause')
                              ->pluck('count', 'cause');
                              
        $topReturnedSkus = CsCreditNoteDetail::select('sku', CsCreditNoteDetail::raw('SUM(quantity_returned) as total_returned'))
                                             ->groupBy('sku')
                                             ->orderBy('total_returned', 'desc')
                                             ->take(10)
                                             ->get();
        
        $topCustomers = CsCreditNote::selectRaw('customer_name, count(*) as count')
                                    ->groupBy('customer_name')
                                    ->orderBy('count', 'desc')
                                    ->take(10)
                                    ->pluck('count', 'customer_name');
        
        $topWarehouses = CsCreditNote::join('cs_warehouses', 'cs_credit_notes.warehouse_id', '=', 'cs_warehouses.id')
                                     ->selectRaw('cs_warehouses.name, count(*) as count')
                                     ->groupBy('cs_warehouses.name')
                                     ->orderBy('count', 'desc')
                                     ->take(10)
                                     ->pluck('count', 'cs_warehouses.name');
        
        $latestNotes = CsCreditNote::with('order')->latest()->take(5)->get();

        return view('customer-service.credit-notes.dashboard', compact('requestTypes', 'monthlyCounts', 'causes', 'topReturnedSkus', 'topCustomers', 'topWarehouses', 'latestNotes'));
    }
}