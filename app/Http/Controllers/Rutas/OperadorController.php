<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Factura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\EventRegistrationService;

class OperadorController extends Controller
{
    public function showLoginForm()
    {
        return view('rutas.operador.login');
    }

    public function accessGuia(Request $request)
    {
        $request->validate(['guia' => 'required|string']);

        $guia = Guia::where('guia', $request->input('guia'))->first();

        if (!$guia) {
            return back()->with('error', 'El número de guía no existe.');
        }

        if ($guia->estatus === 'En Espera') {
            return back()->with('error', 'Esta guía aún no ha sido asignada a una ruta.');
        }

        if ($guia->estatus === 'Completada') {
            return back()->with('error', 'Esta guía ya ha sido completada.');
        }

        return redirect()->route('operador.guia.show', ['guia' => $guia->guia]);
    }

    public function showGuia(Guia $guia)
    {
        $guia->load('facturas');

        $googleMapsApiKey = config('app.Maps_api_key');

        return view('rutas.operador.show', compact('guia', 'googleMapsApiKey'));
    }

    public function startRoute(Request $request, Guia $guia)
    {
        $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string',
        ]);

        if ($guia->estatus !== 'Planeada') {
            return back()->with('error', 'Esta ruta no puede ser iniciada.');
        }

        DB::beginTransaction();
        try {
            $guia->estatus = 'Camino a carga';
            $guia->fecha_inicio_ruta = now();
            $guia->save();

            $guia->facturas()->update(['estatus_entrega' => 'Por recolectar']);

            Evento::create([
                'guia_id' => $guia->id,
                'tipo' => 'Sistema',
                'subtipo' => 'Inicio de Viaje',
                'nota' => 'El operador ha iniciado el viaje hacia el punto de carga.',
                'latitud' => $request->input('latitud'),
                'longitud' => $request->input('longitud'),
                'municipio' => $request->input('municipio'),
                'fecha_evento' => now(),
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al iniciar ruta de operador: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al iniciar la ruta.');
        }

        return redirect()->route('operador.guia.show', $guia->guia)->with('success', '¡Viaje iniciado! Dirígete al punto de carga.');
    }

    public function storeEvent(Request $request, Guia $guia, EventRegistrationService $eventService)
    {
        $validatedData = $request->validate([
            'tipo' => 'required|in:Notificacion,Incidencias,Entrega,Sistema',
            'subtipo' => 'required|string|max:255',
            'nota' => 'nullable|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string',
            'factura_ids' => 'nullable|array',
            'factura_ids.*' => 'exists:facturas,id',
            'evidencia' => 'nullable|array',
            'evidencia.*' => 'required_if:subtipo,Entregada,true|required_if:subtipo,No entregada,true|file|max:20480',
        ]);

        try {
            $eventService->handle($guia, $validatedData, $request);
        } catch (\Exception $e) {
            Log::error("Error al guardar evento desde operador: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar el evento.');
        }

        return redirect()->route('operador.guia.show', ['guia' => $guia->guia])->with('success', 'Evento registrado exitosamente.');
    }
}
