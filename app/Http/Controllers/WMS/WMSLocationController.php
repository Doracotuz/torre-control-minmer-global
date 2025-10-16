<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class WMSLocationController extends Controller
{
    public function index()
    {
        $locations = Location::with('warehouse')->latest()->paginate(15);
        return view('wms.locations.index', compact('locations'));
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
                        ->where('bin', '!=', $location->id)
                        ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Esa combinación física de ubicación ya existe.');
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
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=plantilla_ubicaciones.csv',
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
        $records = array_map('str_getcsv', file($path));
        $headers = array_shift($records);

        $lastCode = Location::max(DB::raw('CAST(code AS UNSIGNED)'));
        $nextCode = $lastCode ? $lastCode + 1 : 10001;

        $locationsToInsert = [];
        $now = now();

        foreach ($records as $record) {
            $data = array_combine($headers, $record);
            
            // Usamos el nuevo encabezado 'codigo_almacen'
            $warehouse = \App\Models\Warehouse::where('code', $data['codigo_almacen'])->first();

            if ($warehouse) {
                // Verificamos usando los encabezados en español
                $exists = Location::where('warehouse_id', $warehouse->id)
                    ->where('aisle', $data['pasillo'])
                    ->where('rack', $data['rack'])
                    ->where('shelf', $data['nivel'])
                    ->where('bin', $data['bin'])
                    ->exists();

                if (!$exists) {
                    $locationsToInsert[] = [
                        'warehouse_id' => $warehouse->id,
                        'code' => $nextCode++,
                        // Usamos los encabezados en español para obtener los datos
                        'type' => $data['tipo'],
                        'aisle' => $data['pasillo'],
                        'rack' => $data['rack'],
                        'shelf' => $data['nivel'],
                        'bin' => $data['bin'],
                        'pick_sequence' => $data['secuencia_pick'] ?: null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (!empty($locationsToInsert)) {
            Location::insert($locationsToInsert);
        }
        
        $count = count($locationsToInsert);
        return redirect()->route('wms.locations.index')->with('success', "$count ubicaciones nuevas fueron importadas exitosamente.");
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

}