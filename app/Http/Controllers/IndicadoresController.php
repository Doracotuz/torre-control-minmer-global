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
            'Recursos Humanos' => 'https://app.powerbi.com/reportEmbed?reportId=2bf89056-692c-40ec-910e-6ced646e8c82&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Customer Service' => 'https://app.powerbi.com/reportEmbed?reportId=2bf89056-692c-40ec-910e-6ced646e8c82&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Tráfico' => 'https://app.powerbi.com/reportEmbed?reportId=2bf89056-692c-40ec-910e-6ced646e8c82&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Almacén' => 'https://app.powerbi.com/view?r=eyJrIjoiZTU5MjliODQtMzZiYy00NTkwLWExMGYtOTZlM2FkYzYxYmE3IiwidCI6IjBlMjk4MTZlLTExNmUtNGFiMi1iZjQyLWQyYjgxNWU4NjI4NCIsImMiOjR9',
            'Valor Agregado' => 'https://app.powerbi.com/reportEmbed?reportId=2bf89056-692c-40ec-910e-6ced646e8c82&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Innovación y Desarrollo' => 'https://app.powerbi.com/reportEmbed?reportId=2bf89056-692c-40ec-910e-6ced646e8c82&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Administración' => 'https://app.powerbi.com/reportEmbed?reportId=2bf89056-692c-40ec-910e-6ced646e8c82&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Tráfico Importaciones' => 'https://app.powerbi.com/reportEmbed?reportId=2bf89056-692c-40ec-910e-6ced646e8c82&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
            'Proyectos' => 'https://app.powerbi.com/reportEmbed?reportId=2bf89056-692c-40ec-910e-6ced646e8c82&autoAuth=true&ctid=0e29816e-116e-4ab2-bf42-d2b815e86284',
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