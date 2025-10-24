<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareAsset;
use App\Models\Assignment;
use App\Models\OrganigramMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    /**
     * Muestra el formulario para asignar un activo específico.
     */
    public function create(HardwareAsset $asset)
    {
        if ($asset->status !== 'En Almacén') {
            return redirect()->route('asset-management.assets.show', $asset)
                ->with('error', 'Este activo no está disponible para ser asignado.');
        }

        $members = OrganigramMember::orderBy('name')->get();
        return view('asset-management.assignments.create', compact('asset', 'members'));
    }

    /**
     * Guarda la nueva asignación en la base de datos.
     */
    public function store(Request $request, HardwareAsset $asset)
    {
        $request->validate([
            'organigram_member_id' => 'required|exists:organigram_members,id',
            'assignment_date' => 'required|date',
        ]);

        if ($asset->status !== 'En Almacén') {
            return back()->with('error', 'Este activo ya no está disponible.');
        }

        DB::transaction(function () use ($request, $asset) {
            // 1. Crear el registro de asignación
            $assignment = Assignment::create([
                'hardware_asset_id' => $asset->id,
                'organigram_member_id' => $request->organigram_member_id,
                'assignment_date' => $request->assignment_date,
            ]);

            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Asignación', // O 'Préstamo' en storeLoan
                'notes' => 'Asignado a ' . $assignment->member->name,
                'loggable_id' => $assignment->id,
                'loggable_type' => Assignment::class,
            ]);

            $asset->status = 'Asignado';
            $asset->save();
        });

        return redirect()->route('asset-management.assets.show', $asset)
            ->with('success', 'Activo asignado exitosamente.');
    }

    public function edit(Assignment $assignment)
    {
        $assignment->load('member', 'asset.model');
        $members = OrganigramMember::orderBy('name')->get();

        return view('asset-management.assignments.edit', compact('assignment', 'members'));
    }    

    /**
     * Registra la devolución de un activo.
     */
    public function return(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'return_receipt' => 'nullable|file|mimes:pdf|max:10048',
            'actual_return_date' => 'required|date',
        ]);

        if ($assignment->actual_return_date) {
            return back()->with('error', 'Esta asignación ya ha sido marcada como devuelta.');
        }

        DB::transaction(function () use ($request, $assignment, $validated) {
            $receiptPath = null;
            if ($request->hasFile('return_receipt')) {
                $receiptPath = $request->file('return_receipt')->store('assets/return-receipts', 's3');
            }

            $assignment->actual_return_date = $validated['actual_return_date'];

            $assignment->return_receipt_path = $receiptPath;
            $assignment->save();

            $asset = $assignment->asset;
            $asset->status = 'En Almacén';
            $asset->save();
            
            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Devolución',
                'notes' => 'Devuelto por ' . $assignment->member->name . '. Disponible en almacén.',
                'loggable_id' => $assignment->id,
                'loggable_type' => \App\Models\Assignment::class,
            ]);
        });

        return redirect()->route('asset-management.assets.show', $assignment->asset)
            ->with('success', 'Devolución registrada exitosamente. El activo está disponible en almacén.');
    }

    public function createLoan(HardwareAsset $asset)
    {
        if ($asset->status !== 'En Almacén') {
            return redirect()->route('asset-management.assets.show', $asset)
                ->with('error', 'Este activo no está disponible para ser prestado.');
        }

        $members = OrganigramMember::orderBy('name')->get();
        return view('asset-management.assignments.create-loan', compact('asset', 'members'));
    }

    /**
     * Guarda el nuevo préstamo en la base de datos.
     */
    public function storeLoan(Request $request, HardwareAsset $asset)
    {
        $validated = $request->validate([
            'organigram_member_id' => 'required|exists:organigram_members,id',
            'assignment_date' => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:assignment_date',
        ]);

        if ($asset->status !== 'En Almacén') {
            return back()->with('error', 'Este activo ya no está disponible.');
        }

        DB::transaction(function () use ($validated, $asset) {
            $member = \App\Models\OrganigramMember::find($validated['organigram_member_id']);

            // 1. Crear el registro del préstamo
            $assignment = $asset->assignments()->create([
                'type' => 'Préstamo',
                'organigram_member_id' => $validated['organigram_member_id'],
                'assignment_date' => $validated['assignment_date'],
                'expected_return_date' => $validated['expected_return_date'],
            ]);

            // 2. REGISTRAR EL EVENTO EN EL LOG
            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Préstamo',
                'notes' => 'Prestado a ' . $member->name . ' hasta el ' . \Carbon\Carbon::parse($validated['expected_return_date'])->format('d/m/Y'),
                'loggable_id' => $assignment->id,
                'loggable_type' => \App\Models\Assignment::class,
            ]);

            // 3. Actualizar el estatus del activo
            $asset->status = 'Prestado';
            $asset->save();
        });

        return redirect()->route('asset-management.assets.show', $asset)
            ->with('success', 'Activo prestado exitosamente.');
    }

    public function uploadReceipt(Request $request, Assignment $assignment)
    {
        $request->validate([
            'signed_receipt' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Eliminar el archivo antiguo si existe
        if ($assignment->signed_receipt_path) {
            Storage::disk('s3')->delete($assignment->signed_receipt_path);
        }

        $path = $request->file('signed_receipt')->store('assets/receipts', 's3');
        $assignment->update(['signed_receipt_path' => $path]);

        return back()->with('success', 'Responsiva firmada subida exitosamente.');
    }    

    public function uploadReturnReceipt(Request $request, Assignment $assignment)
    {
        $request->validate([
            'return_receipt' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Eliminar el archivo antiguo si existe
        if ($assignment->return_receipt_path) {
            Storage::disk('s3')->delete($assignment->return_receipt_path);
        }

        $path = $request->file('return_receipt')->store('assets/return-receipts', 's3');
        $assignment->update(['return_receipt_path' => $path]);

        return back()->with('success', 'Responsiva de devolución subida exitosamente.');
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'organigram_member_id' => 'required|exists:organigram_members,id',
            'assignment_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:assignment_date',
            'actual_return_date' => 'nullable|date|after_or_equal:assignment_date',
            'signed_receipt' => 'nullable|file|mimes:pdf|max:2048',
            'return_receipt' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        // Manejar la carga de la responsiva de asignación
        if ($request->hasFile('signed_receipt')) {
            if ($assignment->signed_receipt_path) {
                Storage::disk('s3')->delete($assignment->signed_receipt_path);
            }
            $validated['signed_receipt_path'] = $request->file('signed_receipt')->store('assets/receipts', 's3');
        }

        // Manejar la carga de la responsiva de devolución
        if ($request->hasFile('return_receipt')) {
            if ($assignment->return_receipt_path) {
                Storage::disk('s3')->delete($assignment->return_receipt_path);
            }
            $validated['return_receipt_path'] = $request->file('return_receipt')->store('assets/return-receipts', 's3');
        }
        
        // Si se define una fecha de devolución, pero el estatus del activo sigue
        // "Asignado", debemos actualizar el estatus del activo.
        if ($validated['actual_return_date'] && $assignment->asset->status === 'Asignado') {
             // Solo si esta es la asignación actual
            if ($assignment->asset->currentAssignment?->id == $assignment->id) {
                $assignment->asset->update(['status' => 'En Almacén']);
                
                $assignment->asset->logs()->create([
                    'user_id' => Auth::id(),
                    'action_type' => 'Devolución',
                    'notes' => 'Devuelto por ' . $assignment->member->name . ' (Editado).',
                    'loggable_id' => $assignment->id,
                    'loggable_type' => \App\Models\Assignment::class,
                ]);
            }
        }

        $assignment->update($validated);

        return redirect()->route('asset-management.user-dashboard.show', $assignment->member)
            ->with('success', 'Asignación actualizada exitosamente.');
    }

    public function createImport()
    {
        return view('asset-management.assignments.import');
    }

    /**
     * Descarga la plantilla CSV para la importación masiva.
     */
    public function downloadTemplate()
    {
        $headers = ['asset_tag', 'organigram_member_email', 'assignment_date'];
        $data = [
            ['ACT-001', 'juan.perez@empresa.com', '2025-10-24'],
            ['ACT-002', 'maria.lopez@empresa.com', '2025-10-25'],
        ];

        $csv = Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->insertOne($headers);
        $csv->insertAll($data);
        
        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_asignacion_masiva.csv"',
        ]);
    }

    /**
     * Procesa el CSV de asignaciones masivas.
     */
    public function storeImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        $errors = [];
        $successCount = 0;

        foreach ($records as $index => $record) {
            $rowNum = $index + 2; // +1 por el 0-index, +1 por la cabecera
            Log::info("Procesando fila $rowNum: ", $record);

            // 1. Validar datos del CSV (Regla de fecha actualizada)
            $validator = Validator::make($record, [
                'asset_tag' => 'required|string',
                'organigram_member_email' => 'required|email',
                'assignment_date' => 'required|string', // <-- CAMBIO: Se valida solo que exista
            ]);

            if ($validator->fails()) {
                $errors[] = "Fila $rowNum: Datos inválidos o faltantes. ({$validator->errors()->first()})";
                continue;
            }

            // --- INICIO DE NUEVO PARSEO DE FECHA ---
            $parsedDate = null;
            $dateString = trim($record['assignment_date']);
            
            try {
                // Intenta formato YYYY-MM-DD (Ej: 2025-10-24)
                $parsedDate = Carbon::createFromFormat('Y-m-d', $dateString);
            } catch (\Exception $e) {
                try {
                    // Si falla, intenta formato DD/MM/YYYY (Ej: 24/10/2025)
                    $parsedDate = Carbon::createFromFormat('d/m/Y', $dateString);
                } catch (\Exception $e2) {
                    try {
                        // Si falla, intenta formato MM/DD/YYYY (Ej: 10/24/2025)
                        $parsedDate = Carbon::createFromFormat('m/d/Y', $dateString);
                    } catch (\Exception $e3) {
                         // Si todos fallan, reporta el error
                         $errors[] = "Fila $rowNum: El formato de fecha '{$dateString}' no es válido. Usa YYYY-MM-DD o DD/MM/YYYY.";
                         continue;
                    }
                }
            }
            
            // Si llegamos aquí, $parsedDate es un objeto Carbon válido
            $formattedDate = $parsedDate->format('Y-m-d'); // Formatear para la BD
            // --- FIN DE NUEVO PARSEO DE FECHA ---


            // 2. Validar Lógica de Negocio
            $asset = HardwareAsset::where('asset_tag', $record['asset_tag'])->first();
            if (!$asset) {
                $errors[] = "Fila $rowNum: No se encontró el activo con etiqueta '{$record['asset_tag']}'.";
                continue;
            }

            if ($asset->status !== 'En Almacén') {
                $errors[] = "Fila $rowNum: El activo '{$record['asset_tag']}' no está 'En Almacén'. Su estado actual es '{$asset->status}'. No se puede asignar.";
                continue;
            }

            $member = OrganigramMember::where('email', $record['organigram_member_email'])->first();
            if (!$member) {
                $errors[] = "Fila $rowNum: No se encontró al miembro con email '{$record['organigram_member_email']}'.";
                continue;
            }

            // 3. Procesar la asignación (usando transacción)
            try {
                DB::transaction(function () use ($asset, $member, $formattedDate) { // <-- CAMBIO
                    $assignment = Assignment::create([
                        'hardware_asset_id' => $asset->id,
                        'organigram_member_id' => $member->id,
                        'assignment_date' => $formattedDate, // <-- CAMBIO: Usar la fecha parseada
                    ]);

                    $asset->logs()->create([
                        'user_id' => Auth::id(),
                        'action_type' => 'Asignación',
                        'notes' => 'Asignado a ' . $member->name . ' (Importación Masiva)',
                        'loggable_id' => $assignment->id,
                        'loggable_type' => Assignment::class,
                    ]);

                    $asset->status = 'Asignado';
                    $asset->save();
                });
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Error procesando Fila $rowNum: " . $e->getMessage(), $record);
                $errors[] = "Fila $rowNum: Error inesperado en la base de datos. ({$e->getMessage()})";
            }
        }

        if (empty($errors)) {
            return redirect()->route('asset-management.dashboard')->with('success', "Se han importado y asignado $successCount activos exitosamente.");
        } else {
            $errorMessage = "Se completaron $successCount asignaciones, pero ocurrieron errores: <br><ul><li>" . implode('</li><li>', $errors) . "</li></ul>";
            return redirect()->route('asset-management.assignments.import.create')->with('error', $errorMessage);
        }
    }


}