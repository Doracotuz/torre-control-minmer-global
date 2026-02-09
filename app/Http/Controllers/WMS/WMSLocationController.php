<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;

class WMSLocationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            if ($request->routeIs('wms.locations.index') || $request->routeIs('wms.locations.show') || $request->routeIs('wms.locations.export') || $request->routeIs('wms.locations.filter')) {
                if (!$user->hasFfPermission('wms.locations.view')) {
                    abort(403, 'No tienes permiso para ver ubicaciones.');
                }
            } elseif ($request->routeIs('wms.locations.create') || $request->routeIs('wms.locations.store') || $request->routeIs('wms.locations.edit') || $request->routeIs('wms.locations.update') || $request->routeIs('wms.locations.destroy') || $request->routeIs('wms.locations.import') || $request->routeIs('wms.locations.template')) {
                if (!$user->hasFfPermission('wms.locations.manage')) {
                    abort(403, 'No tienes permiso para gestionar ubicaciones.');
                }
            } elseif ($request->routeIs('wms.locations.print')) {
                if (!$user->hasFfPermission('wms.locations.print')) {
                    abort(403, 'No tienes permiso para imprimir etiquetas de ubicación.');
                }
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Location::with('warehouse')->latest();

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('aisle')) {
            $query->where('aisle', $request->aisle);
        }
        if ($request->filled('rack')) {
            $query->where('rack', $request->rack);
        }
        if ($request->filled('shelf')) {
            $query->where('shelf', $request->shelf);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $locations = $query->paginate(25)->withQueryString();

        $total_locations = Location::count();
        $kpis = [
            'total_locations' => $total_locations,
            'storage' => Location::where('type', 'storage')->count(),
            'picking' => Location::where('type', 'picking')->count(),
            'receiving' => Location::where('type', 'receiving')->count(),
            'shipping' => Location::where('type', 'shipping')->count(),
            'quality_control' => Location::where('type', 'quality_control')->count(),
        ];

        $warehouses = Warehouse::orderBy('name')->get();
        $aislesQuery = Location::select('aisle')->whereNotNull('aisle')->distinct();
        if ($request->filled('warehouse_id')) {
            $aislesQuery->where('warehouse_id', $request->warehouse_id);
        }
        $aisles = $aislesQuery->orderBy('aisle')->pluck('aisle');
        $racksQuery = Location::select('rack')->whereNotNull('rack')->distinct();
        if ($request->filled('warehouse_id')) {
            $racksQuery->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('aisle')) {
            $racksQuery->where('aisle', $request->aisle);
        }
        $racks = $racksQuery->orderBy('rack')->pluck('rack');
        $shelvesQuery = Location::select('shelf')->whereNotNull('shelf')->distinct();
        if ($request->filled('warehouse_id')) {
            $shelvesQuery->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('aisle')) {
            $shelvesQuery->where('aisle', $request->aisle);
        }
        if ($request->filled('rack')) {
            $shelvesQuery->where('rack', $request->rack);
        }
        $shelves = $shelvesQuery->orderBy('shelf')->pluck('shelf');
        $filters = [
            'warehouses' => $warehouses,
            'aisles' => $aisles,
            'racks' => $racks,
            'shelves' => $shelves,
        ];

        return view('wms.locations.index', compact('locations', 'kpis', 'filters'));
    }

    public function fetchFilteredIds(Request $request)
    {
        $query = Location::query();

        if ($request->filled('warehouse_id')) { $query->where('warehouse_id', $request->warehouse_id); }
        if ($request->filled('aisle')) { $query->where('aisle', $request->aisle); }
        if ($request->filled('rack')) { $query->where('rack', $request->rack); }
        if ($request->filled('shelf')) { $query->where('shelf', $request->shelf); }
        if ($request->filled('type')) { $query->where('type', $request->type); }

        $ids = $query->pluck('id');
        return response()->json($ids);
    }

    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('wms.locations.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'aisle' => 'nullable|string|max:255',
            'rack' => 'nullable|string|max:255',
            'shelf' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'type' => 'required|string',
            'pick_sequence' => 'nullable|integer|min:0',
        ]);

        $exists = Location::where('warehouse_id', $validatedData['warehouse_id'])
                        ->where('aisle', $validatedData['aisle'])
                        ->where('rack', $validatedData['rack'])
                        ->where('shelf', $validatedData['shelf'])
                        ->where('bin', $validatedData['bin'])
                        ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Esa combinación física de ubicación ya existe.');
        }

        $lastCode = Location::max(DB::raw('CAST(code AS UNSIGNED)'));
        
        $validatedData['code'] = $lastCode ? $lastCode + 1 : 10001;

        Location::create($validatedData);

        return redirect()->route('wms.locations.index')
                        ->with('success', 'Ubicación creada. Código asignado: ' . $validatedData['code']);
    }

    public function show(Location $location) 
    {
        return view('wms.locations.show', compact('location'));
    }   

    public function edit(Location $location)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('wms.locations.edit', compact('location', 'warehouses'));
    }

    public function update(Request $request, Location $location)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'aisle' => 'nullable|string|max:255',
            'rack' => 'nullable|string|max:255',
            'shelf' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'type' => 'required|string',
            'pick_sequence' => 'nullable|integer|min:0',
        ]);
        
        $exists = Location::where('warehouse_id', $validatedData['warehouse_id'])
                        ->where('aisle', $validatedData['aisle'])
                        ->where('rack', $validatedData['rack'])
                        ->where('shelf', $validatedData['shelf'])
                        ->where('bin', $validatedData['bin'])
                        ->where('id', '!=', $location->id) 
                        ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Esa combinación física de ubicación ya existe en el sistema.');
        }

        $location->update($validatedData);

        return redirect()->route('wms.locations.index')
                        ->with('success', 'Ubicación actualizada exitosamente.');
    }

    public function destroy(Location $location)
    {
        try {
            $location->delete();
            return redirect()->route('wms.locations.index')
                             ->with('success', 'Ubicación eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('wms.locations.index')
                             ->with('error', 'No se puede eliminar la ubicación porque tiene inventario asociado.');
        }
    }

    public function downloadTemplate()
    {
        $fileName = 'plantilla_ubicaciones.csv';
        
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = [
            'codigo_almacen', 
            'tipo', 
            'pasillo', 
            'rack', 
            'nivel', 
            'bin', 
            'secuencia_pick'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);
        $file = $request->file('file');
        $path = $file->getRealPath();
        
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);
        $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        $header = array_map('trim', $header);

        if (!in_array('codigo_almacen', $header)) {
            return back()->with('error', 'Error en el archivo: No se encontró la columna "codigo_almacen". Verifique la plantilla.');
        }

        $lastCode = Location::max(DB::raw('CAST(code AS UNSIGNED)'));
        $nextCode = $lastCode ? $lastCode + 1 : 10001;

        $locationsToInsert = [];
        $now = now();
        $importedCount = 0;

        foreach ($rows as $row) {
            if (count($header) != count($row)) continue;
            
            $data = array_combine($header, $row);
            
            $warehouse = \App\Models\Warehouse::where('code', trim($data['codigo_almacen']))->first();

            if ($warehouse) {
                $exists = Location::where('warehouse_id', $warehouse->id)
                    ->where('aisle', trim($data['pasillo']))
                    ->where('rack', trim($data['rack']))
                    ->where('shelf', trim($data['nivel']))
                    ->where('bin', trim($data['bin']))
                    ->exists();

                if (!$exists) {
                    $locationsToInsert[] = [
                        'warehouse_id' => $warehouse->id,
                        'code' => $nextCode++,
                        'type' => strtolower(trim($data['tipo'])),
                        'aisle' => trim($data['pasillo']),
                        'rack' => trim($data['rack']),
                        'shelf' => trim($data['nivel']),
                        'bin' => trim($data['bin']),
                        'pick_sequence' => !empty($data['secuencia_pick']) ? (int)$data['secuencia_pick'] : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $importedCount++;
                }
            }
        }

        if (!empty($locationsToInsert)) {
            foreach (array_chunk($locationsToInsert, 500) as $chunk) {
                Location::insert($chunk);
            }
        }
        
        if ($importedCount == 0) {
            return redirect()->route('wms.locations.index')->with('error', "No se importaron ubicaciones. Verifique que los códigos de almacén existan y que las ubicaciones no estén duplicadas.");
        }

        return redirect()->route('wms.locations.index')->with('success', "$importedCount ubicaciones nuevas fueron importadas exitosamente.");
    }

    public function printLabels(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1']);
        $locations = Location::whereIn('id', $request->ids)->orderBy('pick_sequence')->get();
        if ($locations->isEmpty()) {
            return back()->with('error', 'No se seleccionaron ubicaciones válidas.');
        }
        $pdf = Pdf::loadView('wms.locations.pdf', compact('locations'));
        return $pdf->stream('etiquetas-ubicaciones.pdf');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'reporte_ubicaciones_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($request) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Codigo', 'Almacen', 'Tipo', 'Ubicacion_Completa',
                'Pasillo', 'Rack', 'Nivel', 'Bin', 'Sec_Picking'
            ]);

            $query = Location::with('warehouse');

            if ($request->filled('search')) { $query->where('bin', 'like', '%' . $request->search . '%'); }
            if ($request->filled('aisle')) { $query->where('aisle', $request->aisle); }
            if ($request->filled('rack')) { $query->where('rack', $request->rack); }
            if ($request->filled('shelf')) { $query->where('shelf', $request->shelf); }
            if ($request->filled('type')) { $query->where('type', $request->type); }

            $query->chunk(500, function ($locations) use ($file) {
                foreach ($locations as $location) {
                    fputcsv($file, [
                        $location->code,
                        $location->warehouse->name ?? 'N/A',
                        $location->translated_type,
                        "{$location->aisle}-{$location->rack}-{$location->shelf}-{$location->bin}",
                        $location->aisle,
                        $location->rack,
                        $location->shelf,
                        $location->bin,
                        $location->pick_sequence ?? '',
                    ]);
                }
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }    
}