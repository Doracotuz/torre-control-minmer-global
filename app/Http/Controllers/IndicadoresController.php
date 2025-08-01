<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class IndicadoresController extends Controller
{
    public function show(Folder $folder)
    {
        $area = $folder->area;

        if (!$area) {
            return back()->with('error', 'Esta carpeta no está asociada a un área específica.');
        }

        // URLs para insertar reportes.
        $powerBiUrls = [
            'Recursos Humanos' => 'https://app.powerbi.com/view?r=eyJrIjoiYTA5Y2RlNTgtYWFmZS00YmVkLWE4YmUtMTNhNTc0ZDIxM2ExIiwidCI6IjBlMjk4MTZlLTExNmUtNGFiMi1iZjQyLWQyYjgxNWU4NjI4NCIsImMiOjR9',
            'Customer Service' => 'https://app.powerbi.com/reportEmbed?reportId=e028adcd-c4e7-41bc-ac4d-824d5b6c41c6&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Tráfico' => 'https://app.powerbi.com/reportEmbed?reportId=e028adcd-c4e7-41bc-ac4d-824d5b6c41c6&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Almacén' => 'https://app.powerbi.com/view?r=eyJrIjoiZTU5MjliODQtMzZiYy00NTkwLWExMGYtOTZlM2FkYzYxYmE3IiwidCI6IjBlMjk4MTZlLTExNmUtNGFiMi1iZjQyLWQyYjgxNWU4NjI4NCIsImMiOjR9',
            'Valor Agregado' => 'https://app.powerbi.com/view?r=eyJrIjoiYmQ3ZDQyZDctOThlMS00ZjdmLWI3ZDMtZmZkZDYwMGIwMjdiIiwidCI6IjBlMjk4MTZlLTExNmUtNGFiMi1iZjQyLWQyYjgxNWU4NjI4NCIsImMiOjR9',
            'Innovación y Desarrollo' => '',
            'Administración' => '',
            'Tráfico Importaciones' => '',
            'Proyectos' => '',
            'Brokerage' => 'https://app.powerbi.com/view?r=eyJrIjoiNDJiMjNiMjgtZjAxMC00ZDdlLThhZTQtNGNjNzI3ZDUzNWU3IiwidCI6IjBlMjk4MTZlLTExNmUtNGFiMi1iZjQyLWQyYjgxNWU4NjI4NCIsImMiOjR9',
        ];

        // busca el URL para el área actual.
        $reportUrl = $powerBiUrls[$area->name] ?? null;

        // si no se encuentra un reporte para esa área, regresa con un error.
        if (!$reportUrl) {
            return back()->with('error', 'No hay un reporte de indicadores disponible para el área de ' . $area->name);
        }

        return view('indicadores.show', [
            'reportUrl' => $reportUrl,
            'areaName' => $area->name,
            'currentFolder' => $folder // se define la variable de la carpeta para tener un target cuando se regrese
        ]);
    }
}