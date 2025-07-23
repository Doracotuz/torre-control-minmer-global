<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use App\Models\Factura;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // Importar Carbon para fechas

class OperadorController extends Controller
{
    /**
     * Muestra la página inicial para que el operador ingrese su guía.
     */
    public function index()
    {
        return view('public.operador.index');
    }

    /**
     * Verifica el número de guía y redirige si es válido.
     */
    public function checkGuia(Request $request)
    {
        $request->validate(['guia' => 'required|string']);

        $guia = Guia::where('guia', $request->input('guia'))->first();

        // Si la guía no existe
        if (!$guia) {
            return back()->with('error', 'El número de guía no fue encontrado.');
        }

        // Si la guía está 'Completada', no se puede acceder desde aquí.
        if ($guia->estatus === 'Completada') {
            return back()->with('error', 'Esta guía ya fue completada y no puede ser modificada.');
        }

        // Si la guía está 'En Espera' o 'Planeada', permitir el acceso.
        // Si está 'En Transito', también permitir acceso para continuar la operación.
        if (!in_array($guia->estatus, ['En Espera', 'Planeada', 'En Transito'])) {
            return back()->with('error', 'Esta guía no está en un estado válido para operación.');
        }

        // Si todo está bien, lo redirigimos a la vista de detalles del viaje
        return redirect()->route('operador.show', $guia->guia); // Pasamos el número de guía, no el objeto completo
    }

    /**
     * Muestra los detalles de la guía para el operador.
     */
    public function show($guiaNumber)
    {
        $guia = Guia::where('guia', $guiaNumber)
                    ->with(['facturas', 'ruta.paradas', 'eventos']) // Cargar relaciones necesarias
                    ->firstOrFail();

        // Si la guía ya está completada, no permitir verla por el operador
        if ($guia->estatus === 'Completada') {
             return redirect()->route('operador.index')->with('error', 'Esta guía ya ha sido completada.');
        }

        // Determinar si el botón "Iniciar Ruta" debe ser visible
        $showStartButton = $guia->estatus === 'En Espera' || $guia->estatus === 'Planeada';

        // Determinar si los botones de evento (factura y notificación) deben ser visibles
        $showEventButtons = $guia->estatus === 'En Transito';

        // Preparar las facturas para la vista, añadiendo si tienen eventos de entrega
        $facturas = $guia->facturas->map(function ($factura) {
            $factura->has_event = Evento::where('factura_id', $factura->id)
                                        ->whereIn('subtipo', ['Factura Entregada', 'Factura no entregada'])
                                        ->exists();
            return $factura;
        });

        // Iconos para los eventos predefinidos (coincidir con el frontend)
        $eventIcons = [
            'Factura Entregada' => 'fa-check-circle',
            'Factura no entregada' => 'fa-times-circle',
            'Sanitario' => 'fa-toilet',
            'Alimentos' => 'fa-utensils',
            'Combustible' => 'fa-gas-pump',
            'Pernocta' => 'fa-bed',
            'Percance' => 'fa-car-crash',
            // Puedes agregar un icono genérico para otros eventos si lo implementas
            'Otro' => 'fa-info-circle', // Ejemplo de genérico
        ];

        $googleMapsApiKey = config('app.Maps_api_key');


        return view('public.operador.show', compact('guia', 'showStartButton', 'showEventButtons', 'facturas', 'eventIcons', 'googleMapsApiKey'));
    }

    /**
     * Inicia el viaje de una guía.
     */
    public function startTrip(Request $request, Guia $guia)
    {
        if ($guia->estatus === 'En Espera' || $guia->estatus === 'Planeada') {
            $guia->estatus = 'En Transito';
            $guia->fecha_inicio_ruta = Carbon::now();
            $guia->save();

            // Registrar un evento de sistema para el inicio de ruta
            Evento::create([
                'guia_id' => $guia->id,
                'tipo' => 'Sistema',
                'subtipo' => 'Inicio de Ruta',
                'nota' => 'Ruta iniciada por el operador.',
                'latitud' => $request->input('latitud'), // Se debe enviar desde el frontend
                'longitud' => $request->input('longitud'), // Se debe enviar desde el frontend
                'fecha_evento' => Carbon::now(),
            ]);

            return redirect()->route('operador.show', $guia->guia)->with('success', 'Ruta iniciada exitosamente.');
        }
        return back()->with('error', 'La ruta no puede ser iniciada en su estado actual.');
    }

    /**
     * Almacena un evento de entrega de factura.
     */
    public function storeFacturaEvent(Request $request, Guia $guia, Factura $factura)
    {
        $validatedData = $request->validate([
            'subtipo' => 'required|in:Factura Entregada,Factura no entregada',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'nota' => 'nullable|string',
            'evidencia' => 'required|file|mimes:jpeg,png,jpg,gif,mp4|max:50000', // Max 50MB (en KB)
        ]);

        try {
            // Guardar evidencia en S3
            $path = $request->file('evidencia')->store('tms_evidencias', 's3');

            // Crear el evento
            Evento::create([
                'guia_id' => $guia->id,
                'factura_id' => $factura->id,
                'tipo' => 'Entrega',
                'subtipo' => $validatedData['subtipo'],
                'nota' => $validatedData['nota'] ?? $validatedData['subtipo'],
                'url_evidencia' => $path,
                'latitud' => $validatedData['latitud'],
                'longitud' => $validatedData['longitud'],
                'fecha_evento' => Carbon::now(),
            ]);

            // Actualizar el estatus de la factura
            $factura->estatus_entrega = ($validatedData['subtipo'] === 'Factura Entregada') ? 'Entregada' : 'No Entregada';
            $factura->save();

            // Verificar si todas las facturas de la guía están completadas
            $pendingFacturas = $guia->facturas()->where('estatus_entrega', 'Pendiente')->count();
            if ($pendingFacturas === 0) {
                $guia->estatus = 'Completada';
                $guia->fecha_fin_ruta = Carbon::now();
                $guia->save();
            }

            return back()->with('success', 'Evento de factura registrado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al registrar evento de factura (operador): " . $e->getMessage());
            return back()->with('error', 'Error al registrar el evento de factura: ' . $e->getMessage());
        }
    }

    /**
     * Almacena un evento de notificación.
     */
    public function storeNotificationEvent(Request $request, Guia $guia)
    {
        $validatedData = $request->validate([
            'subtipo' => 'required|string|max:255',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'nota' => 'nullable|string',
            'evidencia' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:50000', // Max 50MB (en KB)
        ]);

        try {
            $path = null;
            if ($request->hasFile('evidencia')) {
                $path = $request->file('evidencia')->store('tms_events', 's3');
            }

            Evento::create([
                'guia_id' => $guia->id,
                'tipo' => 'Notificacion',
                'subtipo' => $validatedData['subtipo'],
                'nota' => $validatedData['nota'] ?? $validatedData['subtipo'],
                'url_evidencia' => $path,
                'latitud' => $validatedData['latitud'],
                'longitud' => $validatedData['longitud'],
                'fecha_evento' => Carbon::now(),
            ]);

            return back()->with('success', 'Evento de notificación registrado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al registrar evento de notificación (operador): " . $e->getMessage());
            return back()->with('error', 'Error al registrar el evento de notificación: ' . $e->getMessage());
        }
    }
}