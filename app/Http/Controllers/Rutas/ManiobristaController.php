<?php
namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use App\Models\ManiobraEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManiobristaController extends Controller
{
    public function showLoginForm() {
        return view('rutas.maniobrista.login');
    }

    public function accessGuia(Request $request) {
        $request->validate([
            'numero_empleado' => 'required|string|max:50',
            'guia' => 'required|string|exists:guias,guia',
        ]);

        // Aquí puedes añadir una lógica para verificar si el empleado ya completó esta guía
        $flujoCompleto = ManiobraEvento::where('guia_id', Guia::where('guia', $request->guia)->first()->id)
            ->where('numero_empleado', $request->numero_empleado)
            ->where('evento_tipo', 'Entrega de evidencias')
            ->exists();

        if ($flujoCompleto) {
            return back()->with('error', 'Este número de empleado ya ha completado el flujo para esta guía.');
        }

        return redirect()->route('maniobrista.guia.show', [
            'guia' => $request->guia,
            'empleado' => $request->numero_empleado
        ]);
    }

    public function showGuia(Guia $guia, $empleado) {
        $guia->load(['facturas', 'maniobraEventos' => function($query) use ($empleado) {
            $query->where('numero_empleado', $empleado);
        }]);

        $eventosRealizados = $guia->maniobraEventos->pluck('evento_tipo');
        $siguienteEvento = 'Llegada a carga';
        if ($eventosRealizados->contains('Llegada a carga')) $siguienteEvento = 'Inicio de ruta';
        if ($eventosRealizados->contains('Inicio de ruta')) $siguienteEvento = 'Llegada a destino';
        if ($eventosRealizados->contains('Llegada a destino')) $siguienteEvento = 'Entrega de evidencias';
        if ($eventosRealizados->contains('Entrega de evidencias')) $siguienteEvento = 'Completado';

        return view('rutas.maniobrista.show', [
            'guia' => $guia, 
            'siguienteEvento' => $siguienteEvento, 
            'numero_empleado' => $empleado,
            'googleMapsApiKey' => config('app.Maps_api_key')
        ]);
    }

    public function storeEvent(Request $request, Guia $guia, $empleado) {
        $validated = $request->validate([
            'evento_tipo' => 'required|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string',
            'evidencia' => 'required|image|max:20480', // max 20MB
        ]);

        $path = $request->file('evidencia')->store('maniobras/' . $guia->guia, 's3');

        ManiobraEvento::create([
            'guia_id' => $guia->id,
            'numero_empleado' => $empleado,
            'evento_tipo' => $validated['evento_tipo'],
            'latitud' => $validated['latitud'],
            'longitud' => $validated['longitud'],
            'municipio' => $validated['municipio'],
            'evidencia_path' => $path,
        ]);

        return back()->with('success', 'Evento "'.$validated['evento_tipo'].'" registrado.');
    }

    public function storeFacturaEvidencias(Request $request, Guia $guia, $empleado)
    {
        $validated = $request->validate([
            // Nuevos campos para la ubicación
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string',
            // El array de evidencias ahora es opcional
            'evidencias' => 'nullable|array',
            'evidencias.*' => 'array|max:3',
            'evidencias.*.*' => 'image|max:20480', // max 20MB
        ]);

        DB::transaction(function () use ($validated, $guia, $empleado) {
            // Solo guardar evidencias si el usuario envió archivos
            if (isset($validated['evidencias'])) {
                foreach ($validated['evidencias'] as $facturaId => $files) {
                    foreach ($files as $file) {
                        $path = $file->store('factura_evidencias/' . $guia->guia, 's3');
                        $guia->facturas()->find($facturaId)->evidenciasManiobra()->create([
                            'numero_empleado' => $empleado,
                            'evidencia_path' => $path,
                        ]);
                    }
                }
            }
            
            // Registrar que el flujo de "Entrega de evidencias" se completó, ahora con coordenadas
            ManiobraEvento::firstOrCreate([
                'guia_id' => $guia->id,
                'numero_empleado' => $empleado,
                'evento_tipo' => 'Entrega de evidencias',
            ], [
                'latitud' => $validated['latitud'], 
                'longitud' => $validated['longitud'],
                'municipio' => $validated['municipio'],
                'evidencia_path' => 'N/A' // La evidencia real está en la tabla 'factura_evidencias'
            ]);
        });

        return back()->with('success', 'Evidencias de facturas guardadas exitosamente.');
    }
}