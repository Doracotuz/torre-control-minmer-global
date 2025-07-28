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
        
        $mesesOrden = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $chartData = [];
        
        // Gráfico 1: Cantidad por Mes (Líneas)
        $cantidadPorMes = KpiGeneral::select(DB::raw('mes, SUM(cantidad) as total'))
            ->groupBy('mes')->orderBy(DB::raw('FIELD(mes, "'.implode('","', $mesesOrden).'")'))->pluck('total', 'mes');
        $chartData['linea_mes_labels'] = $cantidadPorMes->keys();
        $chartData['linea_mes_data'] = $cantidadPorMes->values();
        
        // Gráfico 2: Cantidad por Zona (Barras)
        $cantidadPorZona = KpiGeneral::select(DB::raw('zona, SUM(cantidad) as total'))->groupBy('zona')->pluck('total', 'zona');
        $chartData['barras_zona_labels'] = $cantidadPorZona->keys();
        $chartData['barras_zona_data'] = $cantidadPorZona->values();
        
        // Gráfico 3: Cantidad por Área (Pastel)
        $cantidadPorArea = KpiGeneral::select(DB::raw('area, SUM(cantidad) as total'))->groupBy('area')->pluck('total', 'area');
        $chartData['pastel_area_labels'] = $cantidadPorArea->keys();
        $chartData['pastel_area_data'] = $cantidadPorArea->values();

        // Gráfico 4: Porcentaje por Concepto (Dona)
        $porcentajePorConcepto = KpiTiempo::select(DB::raw('concepto, AVG(porcentaje) as promedio'))->groupBy('concepto')->pluck('promedio', 'concepto');
        $chartData['dona_concepto_labels'] = $porcentajePorConcepto->keys();
        $chartData['dona_concepto_data'] = $porcentajePorConcepto->values();

        // Gráfico 5: Porcentaje Promedio por Mes (Barras Horizontales)
        $porcentajePorMes = KpiTiempo::select(DB::raw('mes, AVG(porcentaje) as promedio'))
            ->groupBy('mes')->orderBy(DB::raw('FIELD(mes, "'.implode('","', $mesesOrden).'")'))->pluck('promedio', 'mes');
        $chartData['barras_h_mes_labels'] = $porcentajePorMes->keys();
        $chartData['barras_h_mes_data'] = $porcentajePorMes->values();

        // Gráfico 6: Cantidad por Concepto (Área Polar)
        $cantidadPorConcepto = KpiGeneral::select(DB::raw('concepto, SUM(cantidad) as total'))->groupBy('concepto')->pluck('total', 'concepto');
        $chartData['polar_concepto_labels'] = $cantidadPorConcepto->keys();
        $chartData['polar_concepto_data'] = $cantidadPorConcepto->values();

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