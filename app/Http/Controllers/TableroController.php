<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Models\KpiGeneral;
use App\Models\KpiTiempo;
use Illuminate\Support\Facades\DB;

class TableroController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->is_client && !($user->is_area_admin && $user->area?->name === 'Administración')) {
            abort(403, 'Acceso no autorizado.');
        }

        $folders = $user->accessibleFolders()->whereNull('parent_id')->with('area')->get();

        // 2. Ordenamos la colección para que "Brockerage" aparezca primero.
        $accessibleRootFolders = $folders->sortBy(function ($folder) {
            // Si la carpeta pertenece al área "Brockerage", se le da prioridad (0).
            // A todas las demás se les da una prioridad menor (1).
            return ($folder->area && $folder->area->name === 'Brokerage') ? 0 : 1;
        });
        
        $kpiGeneralesData = KpiGeneral::all();
        $kpisTimeData = KpiTiempo::all();

        $chartData = [];
        if ($kpiGeneralesData->isNotEmpty() && $kpisTimeData->isNotEmpty()) {
            
            $mesesOrden = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $zonas = $kpiGeneralesData->pluck('zona')->unique()->sort()->values();
            $años = $kpiGeneralesData->pluck('ano')->unique()->sort()->values();
            $colores = ['#2c3856', '#ff9c00', '#4a5d8c', '#ffc107', '#6c757d', '#f86c6b', '#20c997'];

            $pivotData = function($data, $filtroConcepto, $groupByCol, $seriesCol, $valueCol, $labels) use ($colores) {
                $datasets = [];
                $seriesValues = $data->where('concepto', 'like', $filtroConcepto)->pluck($seriesCol)->unique()->sort()->values();
                
                foreach($seriesValues as $index => $serie) {
                    $pivoted = $data->where('concepto', 'like', $filtroConcepto)->where($seriesCol, $serie)->groupBy($groupByCol)->map(fn($group) => $group->sum($valueCol));
                    $datasetData = $labels->map(fn($label) => $pivoted->get($label, 0))->values();
                    $datasets[] = [
                        'label'           => $serie,
                        'data'            => $datasetData,
                        'borderColor'     => $colores[$index % count($colores)],
                        'backgroundColor' => $colores[$index % count($colores)],
                        'tension'         => 0.4, // Interpolación suave
                        'fill'            => false,
                    ];
                }
                return ['labels' => $labels, 'datasets' => $datasets];
            };
            
            $doughnutData = function($data, $filtroConcepto, $labelCol, $valueCol) use ($colores) {
                $pivoted = $data->where('concepto', 'like', $filtroConcepto)
                                ->groupBy($labelCol)
                                ->map(fn($group) => $group->sum($valueCol));

                $labels = $pivoted->keys()->sort()->values();
                $datasetData = $labels->map(fn($label) => $pivoted->get($label, 0))->values();
                $bgColors = $labels->map(fn($label, $index) => $colores[$index % count($colores)])->values();

                return [
                    'labels' => $labels,
                    'datasets' => [[
                        'data' => $datasetData,
                        'backgroundColor' => $bgColors,
                        'borderColor' => '#ffffff',
                    ]]
                ];
            };

            // --- KPIGENERALES ---
            $chartData['embarquesPorZonaAño'] = $pivotData($kpiGeneralesData, 'Cantidad de embarques', 'zona', 'ano', 'cantidad', $zonas);
            $chartData['expeditadosPorZonaAño'] = $pivotData($kpiGeneralesData, 'Expeditados requeridos por cliente', 'zona', 'ano', 'cantidad', $zonas);
            $chartData['documentosPorZonaAño'] = $doughnutData($kpiGeneralesData, 'Documentos', 'zona', 'cantidad'); // Gráfico de dona
            $chartData['embarquesPorMesZona'] = $pivotData($kpiGeneralesData, 'Cantidad de embarques', 'mes', 'zona', 'cantidad', collect($mesesOrden));
            $chartData['expeditadosPorMesZona'] = $pivotData($kpiGeneralesData, 'Expeditados requeridos por cliente', 'mes', 'zona', 'cantidad', collect($mesesOrden));
            
            // --- KPIS_TIME ---
            $chartData['tiempoPorZonaAño'] = $pivotData($kpisTimeData, 'Entregas a tiempo', 'zona', 'ano', 'porcentaje', $zonas);
            $chartData['tiempoPorMesAño'] = $pivotData($kpisTimeData, 'Entregas a tiempo', 'mes', 'ano', 'porcentaje', collect($mesesOrden));
        }

        return view('tablero.index', ['accessibleRootFolders' => $accessibleRootFolders, 'chartData' => $chartData]);
    }

    public function uploadKpis(Request $request)
    {
        $request->validate(['kpi_generales_file' => 'nullable|file|mimes:csv,txt', 'kpis_time_file' => 'nullable|file|mimes:csv,txt']);
        
        if ($request->hasFile('kpi_generales_file')) {
            $path = $request->file('kpi_generales_file')->getRealPath();
            Artisan::call('kpi:import', ['file' => $path, 'type' => 'generales']);
        }
        if ($request->hasFile('kpis_time_file')) {
            $path = $request->file('kpis_time_file')->getRealPath();
            Artisan::call('kpi:import', ['file' => $path, 'type' => 'tiempo']);
        }
        return back()->with('success', 'Datos de KPIs importados y actualizados correctamente.');
    }
}