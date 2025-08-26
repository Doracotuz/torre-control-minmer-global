<?php

namespace App\Http\Controllers;

use App\Models\CsOrder;
use App\Models\CsOrderEvent;
use App\Models\Guia;
use App\Models\AuditIncidencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AuditController extends Controller
{
    /**
     * Muestra el dashboard principal para auditores con las cargas del día.
     */
    public function index(Request $request)
    {
        $query = Guia::whereIn('estatus', ['Planeada', 'En Cortina'])
                     ->with('plannings.order');

        $fecha = $request->input('fecha_carga', now()->format('Y-m-d'));
        $query->whereDate('fecha_asignacion', $fecha);

        $cargasDelDia = $query->get();
        return view('audit.index', compact('cargasDelDia', 'fecha'));
    }

    /**
     * Muestra el formulario para la primera auditoría (Almacén).
     */
    public function showWarehouseAudit($orderId)
    {
        $order = CsOrder::with('details.product', 'details.upc')->findOrFail($orderId);
        return view('audit.warehouse', compact('order'));
    }

    /**
     * Guarda los datos de la auditoría de almacén.
     */
    public function storeWarehouseAudit(Request $request, $orderId)
    {
        $order = CsOrder::findOrFail($orderId);
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.calidad' => 'required|string',
            'observaciones' => 'nullable|string',
            'tarimas_cantidad' => 'required|integer|min:0',
            'tarimas_tipo' => 'required|string',
            'emplayado_correcto' => 'required|boolean',
            'etiquetado_correcto' => 'required|boolean',
            'distribucion_correcta' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $order) {
            foreach ($validated['items'] as $detailId => $data) {
                $detail = $order->details()->find($detailId);
                if ($detail) {
                    $detail->update(['audit_calidad' => $data['calidad']]);
                }
            }
            
            $order->audit_almacen_observaciones = $validated['observaciones'];
            $order->status = 'Listo para Enviar';
            $order->save();

            // Guardar datos adicionales de la auditoría de almacén (necesitarás añadir estas columnas a la tabla cs_orders)
            // $order->update([
            //     'audit_tarimas_cantidad' => $validated['tarimas_cantidad'],
            //     'audit_tarimas_tipo' => $validated['tarimas_tipo'],
            //     'audit_emplayado_correcto' => $validated['emplayado_correcto'],
            //     'audit_etiquetado_correcto' => $validated['etiquetado_correcto'],
            //     'audit_distribucion_correcta' => $validated['distribucion_correcta'],
            // ]);

            CsOrderEvent::create([
                'cs_order_id' => $order->id, 'user_id' => Auth::id(),
                'description' => 'Auditoría de almacén completada por ' . Auth::user()->name . '. Pedido listo para enviar.'
            ]);
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de almacén completada para SO: ' . $order->so_number);
    }

    /**
     * Muestra el formulario para la segunda auditoría (Arribo de Unidad).
     */
    public function showPatioAudit($guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        return view('audit.patio', compact('guia'));
    }

    /**
     * Guarda los datos de la auditoría de patio y envía notificación.
     */
    public function storePatioAudit(Request $request, $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $validated = $request->validate([
            'operador' => 'required|string', 'placas' => 'required|string',
            'arribo_fecha' => 'required|date', 'arribo_hora' => 'required',
            'caja_estado' => 'required|string', 'llantas_estado' => 'required|string',
            'combustible_nivel' => 'required|string',
            'presenta_maniobra' => 'sometimes|boolean',
            'equipo_sujecion' => 'required|string',
            'foto_unidad' => 'required|image|max:5120',
            'foto_llantas' => 'required|image|max:5120',
        ]);

        DB::transaction(function () use ($validated, $guia, $request) {
            $fotoUnidadPath = $request->file('foto_unidad')->store('audit_patio_unidad', 's3');
            $fotoLlantasPath = $request->file('foto_llantas')->store('audit_patio_llantas', 's3');

            $guia->update([
                'operador' => $validated['operador'], 'placas' => $validated['placas'],
                'audit_patio_arribo' => $validated['arribo_fecha'] . ' ' . $validated['arribo_hora'],
                'audit_patio_caja_estado' => $validated['caja_estado'],
                'audit_patio_llantas_estado' => $validated['llantas_estado'],
                'audit_patio_combustible_nivel' => $validated['combustible_nivel'],
                'audit_patio_presenta_maniobra' => $request->has('presenta_maniobra'),
                'audit_patio_equipo_sujecion' => $validated['equipo_sujecion'],
                'audit_patio_fotos' => json_encode([
                    'unidad' => $fotoUnidadPath,
                    'llantas' => $fotoLlantasPath,
                ]),
                'estatus' => 'En Cortina',
            ]);

            $order = $guia->plannings->first()->order;
            if ($order) {
                CsOrderEvent::create([
                    'cs_order_id' => $order->id, 'user_id' => Auth::id(),
                    'description' => 'Auditoría de patio completada por ' . Auth::user()->name . ' para la guía ' . $guia->guia . '. Unidad en cortina.'
                ]);
            }

            // TODO: Implementar Mailable y descomentar la línea de envío de correo.
            // Mail::to(['auditoria@tuempresa.com', 'trafico@tuempresa.com'])->send(new UnidadArriboMail($guia));
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de patio completada para Guía: ' . $guia->guia);
    }

    /**
     * Muestra el formulario para la tercera auditoría (Carga de Unidad).
     */
    public function showLoadingAudit($guiaId)
    {
        $guia = Guia::with('facturas')->findOrFail($guiaId);
        return view('audit.loading', compact('guia'));
    }

    /**
     * Guarda los datos de la auditoría de carga.
     */
    public function storeLoadingAudit(Request $request, $guiaId)
    {
        $guia = Guia::findOrFail($guiaId);
        $validated = $request->validate([
            'foto_caja_vacia' => 'required|image|max:5120',
            'fotos_carga' => 'required|array|min:3',
            'fotos_carga.*' => 'required|image|max:5120',
            'marchamo_numero' => 'nullable|string',
            'foto_marchamo' => 'nullable|image|max:5120',
            'lleva_custodia' => 'required|boolean',
            'incidencias' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $guia, $request) {
            // Lógica para guardar archivos... (similar a la auditoría de patio)
            
            if ($request->has('incidencias')) {
                foreach($validated['incidencias'] as $incidencia) {
                    AuditIncidencia::create([
                        'guia_id' => $guia->id,
                        'user_id' => Auth::id(),
                        'tipo_incidencia' => $incidencia,
                    ]);
                }
            }

            $guia->update(['estatus' => 'En Tránsito']); // O el estatus final que definas

            $order = $guia->plannings->first()->order;
            if ($order) {
                CsOrderEvent::create([
                    'cs_order_id' => $order->id, 'user_id' => Auth::id(),
                    'description' => 'Auditoría de carga completada por ' . Auth::user()->name . ' para la guía ' . $guia->guia . '. Unidad en tránsito.'
                ]);
            }
            
            // TODO: Si se modifica, enviar correo a Tráfico.
        });

        return redirect()->route('audit.index')->with('success', 'Auditoría de carga completada para Guía: ' . $guia->guia);
    }
}
