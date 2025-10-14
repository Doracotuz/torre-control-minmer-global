<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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
        // El método validate() ahora devuelve solo los datos seguros
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string|max:255|unique:locations,code',
            'aisle' => 'nullable|string|max:255',
            'rack' => 'nullable|string|max:255',
            'shelf' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'type' => 'required|string',
            'pick_sequence' => 'nullable|integer|min:0',
        ]);

        // Usamos los datos validados
        Location::create($validatedData);

        return redirect()->route('wms.locations.index')
                        ->with('success', 'Ubicación creada exitosamente.');
    }

    public function edit(Location $location)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('wms.locations.edit', compact('location', 'warehouses'));
    }

    public function update(Request $request, Location $location)
    {
        // El método validate() ahora devuelve solo los datos seguros
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string|max:255|unique:locations,code,' . $location->id,
            'aisle' => 'nullable|string|max:255',
            'rack' => 'nullable|string|max:255',
            'shelf' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'type' => 'required|string',
            'pick_sequence' => 'nullable|integer|min:0',
        ]);

        // Usamos los datos validados
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
        $columns = ['warehouse_code', 'code', 'type', 'aisle', 'rack', 'shelf', 'bin', 'pick_sequence'];
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
        $headers = array_shift($records); // Quita los encabezados

        foreach ($records as $record) {
            $data = array_combine($headers, $record);
            $warehouse = \App\Models\Warehouse::where('code', $data['warehouse_code'])->first();
            if ($warehouse) {
                Location::updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'warehouse_id' => $warehouse->id,
                        'type' => $data['type'],
                        'aisle' => $data['aisle'],
                        'rack' => $data['rack'],
                        'shelf' => $data['shelf'],
                        'bin' => $data['bin'],
                        'pick_sequence' => $data['pick_sequence'] ?: null,
                    ]
                );
            }
        }
        return redirect()->route('wms.locations.index')->with('success', 'Ubicaciones importadas exitosamente.');
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