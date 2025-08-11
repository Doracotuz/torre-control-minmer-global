<?php

namespace App\Http\Controllers\Rutas;

use App\Http\Controllers\Controller;
use App\Models\Guia;
use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Factura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Se lee la clave de la API desde la configuración
        $googleMapsApiKey = config('app.Maps_api_key');

        // Se retornan ambas variables ('guia' y 'googleMapsApiKey') a la vista en un solo paso
        return view('rutas.operador.show', compact('guia', 'googleMapsApiKey'));
    }

    public function startRoute(Request $request, Guia $guia)
    {
        $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string', // AÑADIDO
        ]);

        if ($guia->estatus !== 'Planeada') {
            return back()->with('error', 'Esta ruta no puede ser iniciada.');
        }

        DB::beginTransaction();
        try {
            // --- INICIA CAMBIO: Nuevo estatus al iniciar ---
            $guia->estatus = 'Camino a carga';
            $guia->fecha_inicio_ruta = now();
            $guia->save();

            // Actualizar todas las facturas a "Por recolectar"
            $guia->facturas()->update(['estatus_entrega' => 'Por recolectar']);

            Evento::create([
                'guia_id' => $guia->id,
                'tipo' => 'Sistema',
                'subtipo' => 'Inicio de Viaje', // Se cambia de "Inicio de Ruta"
                'nota' => 'El operador ha iniciado el viaje hacia el punto de carga.',
                'latitud' => $request->input('latitud'),
                'longitud' => $request->input('longitud'),
                'municipio' => $request->input('municipio'), // AÑADIDO
                'fecha_evento' => now(),
            ]);
            // --- TERMINA CAMBIO ---
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al iniciar ruta de operador: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al iniciar la ruta.');
        }

        return redirect()->route('operador.guia.show', $guia->guia)->with('success', '¡Viaje iniciado! Dirígete al punto de carga.');
    }

    public function storeEvent(Request $request, Guia $guia)
    {
        // --- INICIA CAMBIO: Validación y lógica de eventos reestructurada ---
        $validatedData = $request->validate([
            'tipo' => 'required|in:Notificacion,Incidencias,Entrega,Sistema', // Se añade 'Sistema'
            'subtipo' => 'required|string|max:255',
            'nota' => 'nullable|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'municipio' => 'nullable|string',
            'factura_ids' => 'nullable|array', // Para eventos que afectan facturas específicas
            'factura_ids.*' => 'exists:facturas,id',
            'evidencia' => 'nullable|array',
            // La evidencia es obligatoria solo si el subtipo es Entregada o No Entregada
            'evidencia.*' => 'required_if:subtipo,Entregada|required_if:subtipo,No Entregada|file|max:20480', // 20MB
        ]);

        try {
            DB::beginTransaction();
            $subtipo = $validatedData['subtipo'];

            // 1. Lógica de cambio de estatus
            switch ($subtipo) {
                case 'Llegada a carga':
                    $guia->estatus = 'En espera de carga';
                    $guia->facturas()->update(['estatus_entrega' => 'En espera de carga']);
                    break;
                case 'Fin de carga':
                    $guia->estatus = 'Por iniciar ruta';
                    $guia->facturas()->update(['estatus_entrega' => 'Por iniciar ruta']);
                    break;
                case 'En ruta':
                    $guia->estatus = 'En tránsito';
                    // Solo actualiza las facturas que no han llegado al cliente o están en pernocta
                    $guia->facturas()->whereIn('estatus_entrega', ['Por iniciar ruta', 'En Pernocta'])->update(['estatus_entrega' => 'En tránsito']);
                    break;
                case 'Pernocta':
                    $guia->estatus = 'En Pernocta';
                    $guia->facturas()->where('estatus_entrega', 'En tránsito')->update(['estatus_entrega' => 'En Pernocta']);
                    break;
                case 'Llegada a cliente':
                    if (!empty($validatedData['factura_ids'])) {
                        Factura::whereIn('id', $validatedData['factura_ids'])->update(['estatus_entrega' => 'En cliente']);
                    }
                    break;
                case 'Proceso de entrega':
                     if (!empty($validatedData['factura_ids'])) {
                        Factura::whereIn('id', $validatedData['factura_ids'])->update(['estatus_entrega' => 'Entregando']);
                    }
                    break;
                case 'Entregada':
                case 'No entregada':
                    if (empty($validatedData['factura_ids'])) {
                        return back()->with('error', 'Debes seleccionar al menos una factura para esta acción.');
                    }
                     if (empty($validatedData['evidencia'])) {
                        return back()->with('error', 'La evidencia es obligatoria para entregas.');
                    }
                    Factura::whereIn('id', $validatedData['factura_ids'])->update(['estatus_entrega' => $subtipo]);
                    break;
            }
            $guia->save();


            // 2. Procesamiento de Evidencias
            $paths = [];
            if ($request->hasFile('evidencia')) {
                // Validación de cantidad
                $fileCount = count($validatedData['evidencia']);
                if (($subtipo === 'Entregada' || $subtipo === 'No entregada') && $fileCount > 10) {
                     return back()->with('error', 'Solo se permiten hasta 10 fotos por factura para eventos de entrega.');
                }
                
                $directory = 'tms_evidencias';
                $facturaNumero = count($validatedData['factura_ids'] ?? []) === 1 ? Factura::find($validatedData['factura_ids'][0])->numero_factura : 'multi';

                foreach ($request->file('evidencia') as $file) {
                    $fileName = $facturaNumero . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $paths[] = $file->storeAs($directory, $fileName, 's3');
                }
            }
            
            // 3. Creación del Evento
            Evento::create([
                'guia_id' => $guia->id,
                // Si el evento afecta múltiples facturas, no se asocia a una sola.
                'factura_id' => (count($validatedData['factura_ids'] ?? []) === 1) ? $validatedData['factura_ids'][0] : null,
                'tipo' => $validatedData['tipo'],
                'subtipo' => $subtipo,
                'nota' => $validatedData['nota'] ?? $subtipo,
                'latitud' => $validatedData['latitud'],
                'longitud' => $validatedData['longitud'],
                'municipio' => $validatedData['municipio'],
                'url_evidencia' => $paths,
                'fecha_evento' => now(),
            ]);

            // 4. Verificar si la guía se ha completado
            $conteoPendientes = $guia->facturas()
                                     ->whereNotIn('estatus_entrega', ['Entregada', 'No entregada'])
                                     ->count();

            if ($conteoPendientes === 0) {
                $guia->estatus = 'Completada';
                $guia->fecha_fin_ruta = now();
                $guia->save();
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al guardar evento desde operador: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar el evento: '.$e->getMessage());
        }
        // --- TERMINA CAMBIO ---

        return redirect()->route('operador.guia.show', ['guia' => $guia->guia])->with('success', 'Evento registrado exitosamente.');
    }
}
