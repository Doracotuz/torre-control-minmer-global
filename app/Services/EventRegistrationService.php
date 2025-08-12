<?php

namespace App\Services;

use App\Models\Guia;
use App\Models\Evento;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventRegistrationService
{
    public function handle(Guia $guia, array $validatedData, Request $request): void
    {
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
                if (!empty($validatedData['factura_ids'])) {
                    Factura::whereIn('id', $validatedData['factura_ids'])->update(['estatus_entrega' => $subtipo]);
                }
                break;
        }
        $guia->save();

        // 2. Procesamiento de Evidencias
        $paths = [];
        if ($request->hasFile('evidencia')) {
            $directory = 'tms_evidencias/' . $guia->guia;
            foreach ($request->file('evidencia') as $file) {
                $fileName = $subtipo . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $paths[] = $file->storeAs($directory, $fileName, 's3');
            }
        }
        
        // 3. Creación del Evento
        Evento::create([
            'guia_id' => $guia->id,
            'factura_id' => (count($validatedData['factura_ids'] ?? []) === 1) ? $validatedData['factura_ids'][0] : null,
            'tipo' => $validatedData['tipo'],
            'subtipo' => $subtipo,
            'nota' => $validatedData['nota'] ?? null,
            'latitud' => $validatedData['latitud'],
            'longitud' => $validatedData['longitud'],
            'municipio' => $validatedData['municipio'] ?? null,
            'url_evidencia' => $paths,
            'fecha_evento' => $validatedData['fecha_evento'] ?? now(),
        ]);

        // 4. Verificar si la guía se ha completado
        $conteoPendientes = $guia->facturas()->whereNotIn('estatus_entrega', ['Entregada', 'No entregada'])->count();
        if ($conteoPendientes === 0) {
            $guia->estatus = 'Completada';
            $guia->fecha_fin_ruta = now();
            $guia->save();
        }
        
        DB::commit();
    }
}