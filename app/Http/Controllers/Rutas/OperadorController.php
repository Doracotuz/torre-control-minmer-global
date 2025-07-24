<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Factura;
use Illuminate\Support\Facades\DB;

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

        // Si es válida ('Planeada' o 'En Transito'), redirigimos
        return redirect()->route('operador.guia.show', ['guia' => $guia->guia]);
    }

    public function showGuia(Guia $guia)
    {
        // Cargamos las facturas para mostrarlas en la lista
        $guia->load('facturas');
        return view('rutas.operador.show', compact('guia'));
    }

    /**
     * Cambia el estatus de la guía a "En Transito" y registra el primer evento.
     */
    public function startRoute(Request $request, Guia $guia)
    {
        $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        if ($guia->estatus !== 'Planeada') {
            return back()->with('error', 'Esta ruta no puede ser iniciada.');
        }

        // 1. Actualizamos la guía
        $guia->estatus = 'En Transito';
        $guia->fecha_inicio_ruta = now();
        $guia->save();

        // 2. Creamos el primer evento "Inicio de Ruta"
        Evento::create([
            'guia_id' => $guia->id,
            'tipo' => 'Sistema',
            'subtipo' => 'Inicio de Ruta',
            'latitud' => $request->input('latitud'),
            'longitud' => $request->input('longitud'),
            'fecha_evento' => now(),
        ]);

        return redirect()->route('operador.guia.show', $guia)->with('success', '¡Ruta iniciada! Buen viaje.');
    }

    public function storeEvent(Request $request, Guia $guia)
    {
        // --- VALIDACIÓN CORREGIDA ---
        $validatedData = $request->validate([
            'tipo' => 'required|in:Entrega,Notificacion,Incidencias',
            'subtipo' => 'required|string|max:255',
            'nota' => 'nullable|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'factura_id' => 'nullable|required_if:tipo,Entrega|exists:facturas,id',
            'evidencia' => 'required|array', // Se asegura de que 'evidencia' sea un array
            'evidencia.*' => 'required|file|max:51200', // Valida CADA archivo dentro del array
        ]);

        // Validación de cantidad de fotos por tipo de evento
        $fileCount = count($validatedData['evidencia']);
        if ($validatedData['tipo'] === 'Entrega' && $fileCount > 10) {
            return back()->with('error', 'Solo se permiten hasta 10 fotos para eventos de entrega.');
        }
        if ($validatedData['tipo'] === 'Notificacion' && $fileCount > 1) {
            return back()->with('error', 'Solo se permite 1 foto para eventos de notificación.');
        }

        try {
            DB::beginTransaction();

            $paths = [];
            if ($request->hasFile('evidencia')) {
                $directory = $validatedData['tipo'] === 'Entrega' ? 'tms_evidencias' : 'tms_events';
                
                $facturaNumero = 'evento';
                if ($validatedData['tipo'] === 'Entrega') {
                    $factura = Factura::find($validatedData['factura_id']);
                    $facturaNumero = $factura->numero_factura;
                }

                // --- LÓGICA DE SUBIDA MEJORADA ---
                foreach ($request->file('evidencia') as $file) {
                    // Generamos un nombre único para evitar sobreescrituras
                    $fileName = $facturaNumero . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $paths[] = $file->storeAs($directory, $fileName, 's3');
                }
            }

            Evento::create([
                'guia_id' => $guia->id,
                'factura_id' => $validatedData['factura_id'] ?? null,
                'tipo' => $validatedData['tipo'],
                'subtipo' => $validatedData['subtipo'],
                'nota' => $validatedData['nota'] ?? $validatedData['subtipo'],
                'latitud' => $validatedData['latitud'],
                'longitud' => $validatedData['longitud'],
                'url_evidencia' => $paths, // Guardamos el array de rutas
                'fecha_evento' => now(),
            ]);

            if ($validatedData['tipo'] === 'Entrega') {
                $factura = Factura::find($validatedData['factura_id']);
                $factura->estatus_entrega = ($validatedData['subtipo'] === 'Factura Entregada') ? 'Entregada' : 'No Entregada';
                $factura->save();

                // Recargamos la relación para obtener el conteo actualizado
                $guia->load('facturas');
                $conteoPendientes = $guia->facturas()->where('estatus_entrega', 'Pendiente')->count();

                if ($conteoPendientes === 0) {
                    $guia->estatus = 'Completada';
                    $guia->fecha_fin_ruta = now();
                    $guia->save();
                }
            }
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // Usamos Log para un mejor registro de errores en producción
            \Illuminate\Support\Facades\Log::error("Error al guardar evento desde operador: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar el evento.');
        }

        return redirect()->route('operador.guia.show', ['guia' => $guia->guia])->with('success', 'Evento registrado exitosamente.');
    }


}