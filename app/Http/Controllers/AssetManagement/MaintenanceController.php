<?php
namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareAsset;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class MaintenanceController extends Controller
{

    public function index(Request $request)
    {
        $query = Maintenance::with(['asset.model.category', 'substituteAsset.model'])
            ->latest('start_date');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('asset', function($q) use ($term) {
                $q->where('asset_tag', 'like', "%$term%")
                ->orWhere('serial_number', 'like', "%$term%")
                ->orWhereHas('model', fn($mq) => $mq->where('name', 'like', "%$term%"));
            })->orWhere('supplier', 'like', "%$term%");
        }

        if ($request->get('status') === 'active') {
            $query->whereNull('end_date');
        } elseif ($request->get('status') === 'completed') {
            $query->whereNotNull('end_date');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $maintenances = $query->paginate(15)->withQueryString();

        $stats = [
            'active' => Maintenance::whereNull('end_date')->count(),
            'completed_month' => Maintenance::whereNotNull('end_date')->whereMonth('end_date', now()->month)->count(),
            'avg_cost' => Maintenance::whereNotNull('cost')->avg('cost') ?? 0,
        ];            

        return view('asset-management.maintenances.index', compact('maintenances', 'stats'));
    }

    public function create(HardwareAsset $asset)
    {
        if (in_array($asset->status, ['En Reparación', 'De Baja'])) {
            return back()->with('error', 'Este activo no puede ser enviado a mantenimiento.');
        }

        $substituteAssets = HardwareAsset::where('status', 'En Almacén')->get();

        return view('asset-management.maintenances.create', compact('asset', 'substituteAssets'));
    }

    public function store(Request $request, HardwareAsset $asset)
    {
        $data = $request->validate([
            'type' => 'required|in:Preventivo,Reparación',
            'start_date' => 'required|date',
            'diagnosis' => 'required|string',
            'supplier' => 'nullable|string',
            'substitute_asset_id' => 'nullable|exists:hardware_assets,id',
        ]);

        DB::transaction(function () use ($data, $asset) {
            $originalUserAssignments = $asset->currentAssignments; 
            $maintenance = $asset->maintenances()->create($data);

            $newStatus = $data['type'] === 'Reparación' ? 'En Reparación' : 'En Mantenimiento';
            $asset->status = $newStatus;
            $asset->save();

            $eventTime = now();
            $startDate = \Carbon\Carbon::parse($data['start_date'])
                            ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);

            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => $newStatus,
                'notes' => 'Enviado a ' . strtolower($newStatus) . ' por: ' . $data['diagnosis'],
                'event_date' => $startDate,
                'loggable_id' => $maintenance->id,
                'loggable_type' => \App\Models\Maintenance::class,
            ]);

            if ($originalUserAssignments->isNotEmpty()) {
                $returnDate = now();
                foreach ($originalUserAssignments as $assignment) {
                    $assignment->actual_return_date = $returnDate;
                    $assignment->save();
                    $asset->logs()->create([
                        'user_id' => Auth::id(),
                        'action_type' => 'Devolución',
                        'notes' => 'Devuelto por ' . $assignment->member->name . ' para ser enviado a mantenimiento.',
                        'loggable_id' => $assignment->id,
                        'loggable_type' => \App\Models\Assignment::class,
                        'event_date' => $returnDate,
                    ]);
                }

                if (!empty($data['substitute_asset_id'])) {
                    $firstUserAssignment = $originalUserAssignments->first();
                    $substitute = HardwareAsset::find($data['substitute_asset_id']);
                    $loanDate = now();
                    
                    $loan = $substitute->assignments()->create([
                        'type' => 'Préstamo',
                        'organigram_member_id' => $firstUserAssignment->organigram_member_id,
                        'assignment_date' => $loanDate,
                    ]);

                    $substitute->status = 'Prestado';
                    $substitute->save();

                    $substitute->logs()->create([
                        'user_id' => Auth::id(),
                        'action_type' => 'Préstamo',
                        'notes' => 'Prestado como sustituto a ' . $firstUserAssignment->member->name,
                        'loggable_id' => $loan->id,
                        'loggable_type' => \App\Models\Assignment::class,
                        'event_date' => $loanDate,
                    ]);
                }
            }
        });

        return redirect()->route('asset-management.assets.show', $asset)
            ->with('success', 'El activo ha sido enviado a mantenimiento exitosamente.');
    }

    public function edit(Maintenance $maintenance)
    {
        $maintenance->load('asset.currentAssignment.member');
        return view('asset-management.maintenances.edit', compact('maintenance'));
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        if ($maintenance->end_date) {
            $user = Auth::user();
            $isSuperAdmin = $user && $user->is_area_admin && $user->area?->name === 'Administración';

            if (!$isSuperAdmin) {
                return back()->with('error', 'Este mantenimiento ya fue finalizado. Solo un Super Administrador puede modificarlo.');
            }
        }
        $data = $request->validate([
            'end_date' => 'nullable|date', 
            'final_asset_status' => 'nullable|in:En Almacén,De Baja',
            'actions_taken' => 'required|string',
            'parts_used' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'photo_1' => 'nullable|image|max:10048',
            'photo_2' => 'nullable|image|max:10048',
            'photo_3' => 'nullable|image|max:10048',
        ]);

        DB::transaction(function () use ($data, $maintenance, $request) { 
            
            if (!empty($data['end_date'])) {
                $eventTime = now();
                $data['end_date'] = \Carbon\Carbon::parse($data['end_date'])
                            ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
            } else {
                $data['end_date'] = null;
            }

            for ($i = 1; $i <= 3; $i++) {
                $fileInputName = "photo_{$i}";
                $dbColumnName = "photo_{$i}_path";
                $removeInputName = "remove_photo_{$i}";

                if ($request->input($removeInputName) === 'true') {
                    if ($maintenance->{$dbColumnName}) {
                        Storage::disk('s3')->delete($maintenance->{$dbColumnName});
                    }
                    $data[$dbColumnName] = null;
                }

                if ($request->hasFile($fileInputName)) {
                    if ($maintenance->{$dbColumnName} && !isset($data[$dbColumnName])) {
                        Storage::disk('s3')->delete($maintenance->{$dbColumnName});
                    }
                    $data[$dbColumnName] = $request->file($fileInputName)->store('maintenances/photos', 's3');
                }
            }

            $maintenance->update($data);

            if (!empty($data['end_date'])) {
                
                $asset = $maintenance->asset;
                $targetStatus = $request->input('final_asset_status', 'En Almacén');

                if ($asset->status !== $targetStatus) {
                    $asset->status = $targetStatus;
                    $asset->save();

                    $logNote = $targetStatus === 'De Baja'
                        ? "Mantenimiento concluido. El equipo fue dictaminado como irreparable / dañado."
                        : "Se completó el mantenimiento. El activo vuelve a Almacén reparado.";

                    $asset->logs()->create([
                        'user_id' => Auth::id(),
                        'action_type' => $targetStatus === 'De Baja' ? 'Baja por Mantenimiento' : 'Mantenimiento Completado',
                        'notes' => $logNote,
                        'event_date' => $data['end_date'],
                        'loggable_id' => $maintenance->id,
                        'loggable_type' => \App\Models\Maintenance::class,
                    ]);
                }

                if ($maintenance->substitute_asset_id) {
                    $substitute = $maintenance->substituteAsset;
                    $loan = $substitute->currentAssignment;

                    if ($loan) {
                        $returnDate = now(); 
                        $loan->actual_return_date = $returnDate;
                        $loan->save();

                        $substitute->status = 'En Almacén';
                        $substitute->save();

                        $substitute->logs()->create([
                            'user_id' => Auth::id(),
                            'action_type' => 'Devolución',
                            'notes' => 'Devuelto por cierre de ticket principal. Fin de préstamo sustituto.',
                            'loggable_id' => $loan->id,
                            'loggable_type' => \App\Models\Assignment::class,
                            'event_date' => $returnDate, 
                        ]);
                    }
                }
            }
        });

        return redirect()->route('asset-management.maintenances.index')
            ->with('success', 'Mantenimiento actualizado y estatus del activo definido.');
    }

    public function generatePdf(Maintenance $maintenance)
    {
        $maintenance->load(['asset.model.category', 'asset.model.manufacturer', 'asset.site']);

        $logoPath = 'LogoAzul.png'; 
        $logoBase64 = null;
        if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($logoPath)) {
            $logoContent = \Illuminate\Support\Facades\Storage::disk('s3')->get($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }
        
        $evidencePhotos = [];
        for ($i = 1; $i <= 3; $i++) {
            $colName = "photo_{$i}_path";
            $path = $maintenance->$colName;

            if ($path && \Illuminate\Support\Facades\Storage::disk('s3')->exists($path)) {
                $fileContent = \Illuminate\Support\Facades\Storage::disk('s3')->get($path);
                $mimeType = \Illuminate\Support\Facades\Storage::disk('s3')->mimeType($path);
                $evidencePhotos[] = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
            }
        }

        $data = [
            'maintenance' => $maintenance,
            'logoBase64' => $logoBase64,
            'evidencePhotos' => $evidencePhotos,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('asset-management.maintenances.certificate', $data);
        $fileName = 'Certificado-Mantenimiento-' . $maintenance->asset->asset_tag . '.pdf';

        return $pdf->stream($fileName);
    }

    public function exportCsv()
    {
        $maintenances = Maintenance::with(['asset.model', 'asset.site'])->latest('start_date')->get();

        $csv = Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->insertOne(['ID', 'Activo', 'Serie', 'Tipo', 'Diagnóstico', 'Proveedor', 'Inicio', 'Fin', 'Costo']);

        foreach ($maintenances as $m) {
            $csv->insertOne([
                $m->id,
                $m->asset->asset_tag ?? 'N/A',
                $m->asset->serial_number ?? 'N/A',
                $m->type,
                $m->diagnosis,
                $m->supplier ?? 'Interno',
                $m->start_date->format('Y-m-d'),
                $m->end_date ? $m->end_date->format('Y-m-d') : 'En Proceso',
                $m->cost ?? 0,
            ]);
        }

        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_mantenimientos_' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function destroy(Maintenance $maintenance)
    {
        $user = Auth::user();
        if (!($user->is_area_admin && $user->area?->name === 'Administración')) {
            abort(403, 'Acceso denegado. Solo el Administrador puede eliminar registros.');
        }

        DB::transaction(function () use ($maintenance) {
            if (!$maintenance->end_date && in_array($maintenance->asset->status, ['En Reparación', 'En Mantenimiento'])) {
                $maintenance->asset->update(['status' => 'En Almacén']);
                
                $maintenance->asset->logs()->create([
                    'user_id' => Auth::id(),
                    'action_type' => 'Cambio de Estatus',
                    'notes' => 'El activo volvió a almacén porque se eliminó su registro de mantenimiento.',
                    'event_date' => now(),
                ]);
            }

            for ($i = 1; $i <= 3; $i++) {
                $path = $maintenance->{"photo_{$i}_path"};
                if ($path && Storage::disk('s3')->exists($path)) {
                    Storage::disk('s3')->delete($path);
                }
            }

            $maintenance->delete();
        });

        return redirect()->route('asset-management.maintenances.index')
            ->with('success', 'Mantenimiento eliminado y archivos borrados correctamente.');
    }    

}