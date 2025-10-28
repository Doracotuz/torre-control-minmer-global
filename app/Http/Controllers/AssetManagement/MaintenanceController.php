<?php
namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareAsset;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{

    public function index()
    {
        $maintenances = Maintenance::with(['asset.model', 'substituteAsset.model'])
            ->latest('start_date')
            ->paginate(15);

        return view('asset-management.maintenances.index', compact('maintenances'));
    }

    public function create(HardwareAsset $asset)
    {
        // Solo se pueden enviar activos que no estén ya en reparación o de baja
        if (in_array($asset->status, ['En Reparación', 'De Baja'])) {
            return back()->with('error', 'Este activo no puede ser enviado a mantenimiento.');
        }

        // Obtenemos activos disponibles para ser usados como sustitutos
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

            // --- INICIO DE MODIFICACIÓN ---
            $eventTime = now();
            $startDate = \Carbon\Carbon::parse($data['start_date'])
                            ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
            // --- FIN DE MODIFICACIÓN ---

            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => $newStatus,
                'notes' => 'Enviado a ' . strtolower($newStatus) . ' por: ' . $data['diagnosis'],
                'event_date' => $startDate, // <-- Usar fecha/hora completa
                'loggable_id' => $maintenance->id,
                'loggable_type' => \App\Models\Maintenance::class,
            ]);

            if ($originalUserAssignments->isNotEmpty()) {
                $returnDate = now(); // La devolución forzada SÍ es 'ahora'
                foreach ($originalUserAssignments as $assignment) {
                    $assignment->actual_return_date = $returnDate;
                    $assignment->save();
                    $asset->logs()->create([
                        'user_id' => Auth::id(),
                        'action_type' => 'Devolución',
                        'notes' => 'Devuelto por ' . $assignment->member->name . ' para ser enviado a mantenimiento.',
                        'loggable_id' => $assignment->id,
                        'loggable_type' => \App\Models\Assignment::class,
                        'event_date' => $returnDate, // <-- ACTUALIZADO
                    ]);
                }

                // C. Si se eligió un sustituto, lo prestamos al *primer* usuario de la lista.
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
                        'event_date' => $loanDate, // <-- ACTUALIZADO
                    ]);
                }
            }
            // --- FIN DE MODIFICACIÓN ---
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
        $data = $request->validate([
            'end_date' => 'required|date|after_or_equal:start_date',
            'actions_taken' => 'required|string',
            'parts_used' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $maintenance) { 
            
            $eventTime = now();
            $endDate = \Carbon\Carbon::parse($data['end_date'])
                        ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
            
            $data['end_date'] = $endDate;

            $maintenance->update($data);

            $asset = $maintenance->asset;
            $asset->status = 'En Almacén';
            $asset->save();

            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Mantenimiento Completado',
                'notes' => "Se completó el mantenimiento. El activo vuelve a Almacén.",
                'event_date' => $endDate,
                'loggable_id' => $maintenance->id,
                'loggable_type' => \App\Models\Maintenance::class,
            ]);

            // 4. Si había un activo sustituto, registrar su devolución.
            if ($maintenance->substitute_asset_id) {
                $substitute = $maintenance->substituteAsset;
                
                // Buscamos el préstamo activo del sustituto.
                $loan = $substitute->currentAssignment;

                if ($loan) {
                    $returnDate = now(); // Usar una variable para consistencia

                    // Finalizamos el préstamo.
                    $loan->actual_return_date = $returnDate;
                    $loan->save();

                    // Actualizamos el estatus del sustituto.
                    $substitute->status = 'En Almacén';
                    $substitute->save();

                    // Registramos la devolución en la línea de vida del sustituto.
                    $substitute->logs()->create([
                        'user_id' => Auth::id(),
                        'action_type' => 'Devolución',
                        'notes' => 'Devuelto por ' . $loan->member->name . '. Fin de préstamo sustituto.',
                        'loggable_id' => $loan->id,
                        'loggable_type' => \App\Models\Assignment::class,
                        'event_date' => $returnDate, // <-- ACTUALIZADO
                    ]);
                }
            }
        });

        return redirect()->route('asset-management.maintenances.index')
            ->with('success', 'Mantenimiento completado. Ambos activos están ahora en Almacén.');
    }

    public function generatePdf(Maintenance $maintenance)
    {
        $maintenance->load(['asset.model.category', 'asset.model.manufacturer', 'asset.site']);

        $logoPath = 'LogoAzul.png'; // Asegúrate que esta es la ruta en tu disco s3
        $logoBase64 = null;
        if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($logoPath)) {
            $logoContent = \Illuminate\Support\Facades\Storage::disk('s3')->get($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }
        
        $data = [
            'maintenance' => $maintenance,
            'logoBase64' => $logoBase64,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('asset-management.maintenances.certificate', $data);
        $fileName = 'Certificado-Mantenimiento-' . $maintenance->asset->asset_tag . '.pdf';

        return $pdf->stream($fileName);
    }    

}