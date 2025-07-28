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

        $accessibleRootFolders = $user->accessibleFolders()->whereNull('parent_id')->with('area')->get();
        
        $kpiGeneralesData = KpiGeneral::all();
        $kpisTimeData = KpiTiempo::all();

        $chartData = [];
        if ($kpiGeneralesData->isNotEmpty() && $kpisTimeData->isNotEmpty()) {
            
            $mesesOrden = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $zonas = $kpiGeneralesData->pluck('zona')->unique()->sort()->values();
            $años = $kpiGeneralesData->pluck('ano')->unique()->sort()->values();
            $colores = ['#2c3856', '#ff9c00', '#4a5d8c', '#ffc107', '#6c757d'];

            // ▼▼ CORRECCIÓN AQUÍ: Se añade "use ($colores)" ▼▼
            $pivotData = function($data, $filtroConcepto, $groupByCol, $seriesCol, $valueCol, $labels) use ($colores) {
                $datasets = [];
                $seriesValues = $data->where('concepto', $filtroConcepto)->pluck($seriesCol)->unique()->sort()->values();
                
                foreach($seriesValues as $index => $serie) {
                    $pivoted = $data->where('concepto', $filtroConcepto)->where($seriesCol, $serie)->groupBy($groupByCol)->map(fn($group) => $group->sum($valueCol));
                    $datasetData = $labels->map(fn($label) => $pivoted->get($label, 0))->values();
                    $datasets[] = [
                        'label' => $serie,
                        'data' => $datasetData,
                        'borderColor' => $colores[$index % count($colores)],
                        'backgroundColor' => $colores[$index % count($colores)],
                        'tension' => 0.1,
                        'fill' => false,
                    ];
                }
                return ['labels' => $labels, 'datasets' => $datasets];
            };

            // --- KPIGENERALES ---
            $chartData['embarquesPorZonaAño'] = $pivotData($kpiGeneralesData, 'Cantidad de embarques', 'zona', 'ano', 'cantidad', $zonas);
            $chartData['expeditadosPorZonaAño'] = $pivotData($kpiGeneralesData, 'Expeditados requeridos por cliente', 'zona', 'ano', 'cantidad', $zonas);
            $chartData['documentosPorZonaAño'] = $pivotData($kpiGeneralesData, 'Documentos', 'zona', 'ano', 'cantidad', $zonas);
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