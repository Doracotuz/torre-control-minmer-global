<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use App\Models\ManiobraEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ManiobristaController extends Controller
{
    public function showLoginForm() {
        return view('rutas.maniobrista.login');
    }

    public function accessGuia(Request $request) {
        $validated = $request->validate([
            'numero_empleado' => 'required|string|max:50',
            'guia' => 'required|string|exists:guias,guia',
        ]);

        $guia = Guia::where('guia', $validated['guia'])->first();
        
        $flujoCompleto = ManiobraEvento::where('guia_id', $guia->id)
            ->where('numero_empleado', $validated['numero_empleado'])
            ->where('evento_tipo', 'Flujo Completado')
            ->exists();

        if ($flujoCompleto) {
            return back()->with('error', 'Este número de empleado ya ha completado el flujo para esta guía.');
        }
        
        return redirect()->route('maniobrista.guia.show', [
            'guia' => $validated['guia'],
            'empleado' => $validated['numero_empleado']
        ]);
    }

    public function showGuia(Guia $guia, $empleado)
    {
        $guia->load([
            'facturas.evidenciasManiobra' => function($query) use ($empleado) {
                $query->where('numero_empleado', $empleado);
            }, 
            'maniobraEventos' => function($query) use ($empleado) {
                $query->where('numero_empleado', $empleado)->orderBy('created_at', 'desc');
            }
        ]);

        $eventosRealizados = $guia->maniobraEventos->pluck('evento_tipo');
        
        // --- LÓGICA DE FLUJO ACTUALIZADA ---
        $estadoActual = 'Llegada a carga';
        if ($eventosRealizados->contains('Llegada a carga')) $estadoActual = 'Inicio de ruta';
        if ($eventosRealizados->contains('Inicio de ruta')) $estadoActual = 'En Ruta (Entregas)';

        $facturasPendientes = $guia->facturas->filter(function ($factura) use ($empleado) {
            return $factura->evidenciasManiobra->where('numero_empleado', $empleado)->isEmpty();
        });

        if ($eventosRealizados->contains('Inicio de ruta') && $facturasPendientes->isEmpty()) {
            $estadoActual = 'Completado';
        }

        return view('rutas.maniobrista.show', [
            'guia' => $guia, 
            'estadoActual' => $estadoActual, 
            'facturasPendientes' => $facturasPendientes,
            'numero_empleado' => $empleado,
            'googleMapsApiKey' => config('app.Maps_api_key')
        ]);
    }

    public function storeEvent(Request $request, Guia $guia, $empleado)
    {
        // AÑADIMOS 'Llegada a destino' A LOS EVENTOS VÁLIDOS
        $validated = $request->validate([
            'evento_tipo' => ['required', \Illuminate\Validation\Rule::in(['Llegada a carga', 'Inicio de ruta', 'Llegada a destino'])],
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string',
            'evidencia' => 'required|image|max:500480',
        ]);
        
        // La validación de duplicados ya no aplica para "Llegada a destino"
        if ($validated['evento_tipo'] !== 'Llegada a destino') {
            $eventoExistente = ManiobraEvento::where('guia_id', $guia->id)
                ->where('numero_empleado', $empleado)
                ->where('evento_tipo', $validated['evento_tipo'])
                ->exists();

            if ($eventoExistente) {
                return back()->with('error', 'El evento "'.$validated['evento_tipo'].'" ya fue registrado.');
            }
        }

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
    
    public function storeFacturaEvidencias(Request $request, Guia $guia, $empleado) {
        $validated = $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string',
            'evidencias' => 'nullable|array',
            'evidencias.*' => 'array|max:3',
            'evidencias.*.*' => 'image|max:500480',
        ]);

        DB::transaction(function () use ($validated, $guia, $empleado) {
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
            
            // Verificamos si todas las facturas ya tienen evidencia
            $guia->load('facturas.evidenciasManiobra'); // Recargamos la relación
            $totalFacturas = $guia->facturas->count();
            $facturasConEvidencia = $guia->facturas->filter(function ($factura) use ($empleado) {
                return $factura->evidenciasManiobra->where('numero_empleado', $empleado)->isNotEmpty();
            })->count();

            if ($totalFacturas === $facturasConEvidencia) {
                // Si todas están completas, se marca el flujo como finalizado
                ManiobraEvento::firstOrCreate([
                    'guia_id' => $guia->id,
                    'numero_empleado' => $empleado,
                    'evento_tipo' => 'Flujo Completado',
                ], ['latitud' => 0, 'longitud' => 0, 'evidencia_path' => 'N/A']);
            }
        });

        return back()->with('success', 'Entregas registradas exitosamente.');
    }
}