<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = $this->getFilteredWarehouses(new Request())->paginate(20);
        return view('customer-service.warehouses.index', compact('warehouses'));
    }

    public function filter(Request $request)
    {
        $warehouses = $this->getFilteredWarehouses($request)->paginate(20);
        return response()->json(['table' => view('customer-service.warehouses.partials.table', compact('warehouses'))->render()]);
    }

    public function create()
    {
        return view('customer-service.warehouses.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|string|max:255|unique:cs_warehouses,warehouse_id',
            'name' => 'required|string|max:255',
        ]);

        CsWarehouse::create($validatedData + ['created_by_user_id' => Auth::id()]);
        return redirect()->route('customer-service.warehouses.index')->with('success', 'Almacén creado exitosamente.');
    }

    public function edit(CsWarehouse $warehouse)
    {
        return view('customer-service.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, CsWarehouse $warehouse)
    {
        $validatedData = $request->validate([
            'warehouse_id' => ['required', 'string', 'max:255', Rule::unique('cs_warehouses')->ignore($warehouse->id)],
            'name' => 'required|string|max:255',
        ]);

        $warehouse->update($validatedData + ['updated_by_user_id' => Auth::id()]);
        return redirect()->route('customer-service.warehouses.index')->with('success', 'Almacén actualizado exitosamente.');
    }

    public function destroy(CsWarehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('customer-service.warehouses.index')->with('success', 'Almacén eliminado exitosamente.');
    }

    public function exportCsv(Request $request)
    {
        $warehouses = $this->getFilteredWarehouses($request)->get();
        $fileName = "export_almacenes_" . date('Y-m-d') . ".csv";
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName" ];

        $callback = function() use ($warehouses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Almacen', 'Nombre', 'Creado por', 'Fecha Creacion']);
            foreach ($warehouses as $warehouse) {
                fputcsv($file, [$warehouse->warehouse_id, $warehouse->name, $warehouse->createdBy->name, $warehouse->created_at->format('Y-m-d')]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $path = $request->file('csv_file')->getRealPath();
        
        $fileContent = file_get_contents($path);
        $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1', true));
        $file = fopen("php://memory", 'r+');
        fwrite($file, $utf8Content);
        rewind($file);

        fgetcsv($file);

        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            if (count(array_filter($row)) == 0) continue;

            CsWarehouse::updateOrCreate(
                ['warehouse_id' => trim($row[0])],
                ['name' => trim($row[1]), 'created_by_user_id' => Auth::id()]
            );
        }
        fclose($file);
        return redirect()->route('customer-service.warehouses.index')->with('success', 'Archivo CSV importado exitosamente.');
    }

    public function downloadTemplate()
    {
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=plantilla_almacenes.csv" ];
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Almacen', 'Almacen']);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    private function getFilteredWarehouses(Request $request)
    {
        $query = CsWarehouse::with('createdBy');
        if ($request->filled('search') && strlen($request->search) > 1) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('warehouse_id', 'like', $searchTerm)->orWhere('name', 'like', $searchTerm));
        }
        return $query->orderBy('name', 'asc');
    }

    public function dashboard()
    {
        $recentWarehouses = CsWarehouse::where('created_at', '>=', Carbon::now()->subYear())
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            ]);

        $topCreators = CsWarehouse::join('users', 'cs_warehouses.created_by_user_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(cs_warehouses.id) as total'))
            ->groupBy('users.name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->pluck('total', 'name');
        
        $totalWarehouses = CsWarehouse::count();


        $chartData = [
            'recentWarehouses' => [
                'labels' => $recentWarehouses->pluck('month'),
                'data' => $recentWarehouses->pluck('count'),
            ],
            'topCreators' => [
                'labels' => $topCreators->keys(),
                'data' => $topCreators->values(),
            ],
            'totalWarehouses' => $totalWarehouses
        ];

        return view('customer-service.warehouses.dashboard', compact('chartData'));
    }

}
