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
        // --- MODIFICACIÓN (Lógica 1-a-N) ---
        // Reemplazamos la comprobación estricta de 'En Almacén'
        $blockedStatuses = ['En Reparación', 'De Baja', 'En Mantenimiento'];
        if (in_array($asset->status, $blockedStatuses)) {
            return redirect()->route('asset-management.assets.show', $asset)
                ->with('error', "Este activo no está disponible para ser asignado. Su estatus es '{$asset->status}'.");
        }
        // --- FIN DE MODIFICACIÓN ---

        $members = OrganigramMember::orderBy('name')->get();
        return view('asset-management.assignments.create', compact('asset', 'members'));
    }

    /**
     * Guarda la nueva asignación en la base de datos.
     */
    public function store(Request $request, HardwareAsset $asset)
    {
        $validated = $request->validate([
            'organigram_member_id' => 'required|exists:organigram_members,id',
            'assignment_date' => 'required|date',
        ]);

        // --- MODIFICACIÓN (Lógica 1-a-N) ---
        $blockedStatuses = ['En Reparación', 'De Baja', 'En Mantenimiento'];
        if (in_array($asset->status, $blockedStatuses)) {
            return back()->with('error', "Este activo no está disponible para ser asignado. Su estatus es: {$asset->status}.");
        }
        // --- FIN DE MODIFICACIÓN ---

        DB::transaction(function () use ($validated, $asset) {
            
            // --- MODIFICACIÓN (Guardar Hora del Evento) ---
            $eventTime = now();
            $eventDate = \Carbon\Carbon::parse($validated['assignment_date'])
                            ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
            // --- FIN DE MODIFICACIÓN ---

            // 1. Crear el registro de asignación
            $assignment = Assignment::create([
                'hardware_asset_id' => $asset->id,
                'organigram_member_id' => $validated['organigram_member_id'],
                'assignment_date' => $eventDate, // <-- Usar fecha/hora completa
            ]);

            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Asignación', 
                'notes' => 'Asignado a ' . $assignment->member->name,
                'loggable_id' => $assignment->id,
                'loggable_type' => Assignment::class,
                'event_date' => $assignment->assignment_date, // <-- Usar fecha/hora completa
            ]);

            // El estatus siempre será 'Asignado' si tiene al menos una asignación
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

            // --- MODIFICACIÓN (Guardar Hora del Evento) ---
            $eventTime = now();
            $fullReturnDate = \Carbon\Carbon::parse($validated['actual_return_date'])
                                ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
            // --- FIN DE MODIFICACIÓN ---

            $assignment->actual_return_date = $fullReturnDate; // <-- Usar fecha/hora completa
            $assignment->return_receipt_path = $receiptPath;
            $assignment->save();

            $asset = $assignment->asset;
            
            // --- MODIFICACIÓN (Lógica 1-a-N) ---
            // Contamos cuántas asignaciones QUEDAN ACTIVAS
            $activeAssignmentsCount = $asset->currentAssignments()->count();

            if ($activeAssignmentsCount == 0) {
                // Si ya no quedan asignaciones activas, vuelve a almacén
                $asset->status = 'En Almacén';
            }
            // Si $activeAssignmentsCount > 0, el estatus NO cambia (sigue "Asignado")
            // --- FIN DE MODIFICACIÓN ---
            
            $asset->save();
            
            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Devolución',
                'notes' => 'Devuelto por ' . $assignment->member->name . '. Disponible en almacén.',
                'loggable_id' => $assignment->id,
                'loggable_type' => \App\Models\Assignment::class,
                'event_date' => $fullReturnDate, // <-- Usar fecha/hora completa
            ]);
        });

        return redirect()->route('asset-management.assets.show', $assignment->asset)
            ->with('success', 'Devolución registrada exitosamente. El activo está disponible en almacén.');
    }

    public function createLoan(HardwareAsset $asset)
    {
        // --- MODIFICACIÓN (Lógica 1-a-N) ---
        $blockedStatuses = ['En Reparación', 'De Baja', 'En Mantenimiento'];
        if (in_array($asset->status, $blockedStatuses)) {
            return redirect()->route('asset-management.assets.show', $asset)
                ->with('error', "Este activo no está disponible para ser prestado. Su estatus es '{$asset->status}'.");
        }
        // --- FIN DE MODIFICACIÓN ---

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

        // --- MODIFICACIÓN (Lógica 1-a-N) ---
        $blockedStatuses = ['En Reparación', 'De Baja', 'En Mantenimiento'];
        if (in_array($asset->status, $blockedStatuses)) {
            return back()->with('error', 'Este activo ya no está disponible.');
        }
        // --- FIN DE MODIFICACIÓN ---

        DB::transaction(function () use ($validated, $asset) {
            $member = \App\Models\OrganigramMember::find($validated['organigram_member_id']);

            // --- MODIFICACIÓN (Guardar Hora del Evento) ---
            $eventTime = now();
            $assignmentDate = \Carbon\Carbon::parse($validated['assignment_date'])
                                ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
            // --- FIN DE MODIFICACIÓN ---

            // 1. Crear el registro del préstamo
            $assignment = $asset->assignments()->create([
                'type' => 'Préstamo',
                'organigram_member_id' => $validated['organigram_member_id'],
                'assignment_date' => $assignmentDate, // <-- Usar fecha/hora completa
                'expected_return_date' => $validated['expected_return_date'],
            ]);

            // 2. REGISTRAR EL EVENTO EN EL LOG
            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Préstamo',
                'notes' => 'Prestado a ' . $member->name . ' hasta el ' . \Carbon\Carbon::parse($validated['expected_return_date'])->format('d/m/Y'),
                'loggable_id' => $assignment->id,
                'loggable_type' => \App\Models\Assignment::class,
                'event_date' => $assignmentDate, // <-- Usar fecha/hora completa
            ]);

            // 3. Actualizar el estatus del activo (siempre 'Asignado' o 'Prestado')
            // Decidimos usar 'Prestado' si al menos una asignación es préstamo.
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

        // --- MODIFICACIÓN (Guardar Hora del Evento) ---
        $eventTime = now();
        // Combinar fecha del formulario con hora actual
        if (isset($validated['assignment_date'])) {
            $validated['assignment_date'] = \Carbon\Carbon::parse($validated['assignment_date'])
                                                    ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
        }
        if (isset($validated['actual_return_date'])) {
            $validated['actual_return_date'] = \Carbon\Carbon::parse($validated['actual_return_date'])
                                                    ->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
        }
        // --- FIN DE MODIFICACIÓN ---

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
        
        // --- MODIFICACIÓN (Lógica 1-a-N) ---
        if ($validated['actual_return_date'] && !$assignment->actual_return_date) {
            // Si se está *registrando* una devolución (no solo editando una existente)
            
            // Primero, actualizamos la asignación
            $assignment->update($validated);
            
            // Contamos cuántas asignaciones activas quedan (usando la relación ya cargada)
            $activeAssignmentsCount = $assignment->asset->currentAssignments()->count();

            if ($activeAssignmentsCount == 0) {
                $assignment->asset->update(['status' => 'En Almacén']);
            }
            
            $assignment->asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Devolución',
                'notes' => 'Devuelto por ' . $assignment->member->name . ' (Editado).',
                'loggable_id' => $assignment->id,
                'loggable_type' => \App\Models\Assignment::class,
                'event_date' => $validated['actual_return_date'], // Usar fecha/hora completa
            ]);
        } else {
            // Si no es una devolución nueva, solo actualizamos los datos
            $assignment->update($validated);
        }
        // --- FIN DE MODIFICACIÓN ---

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
        $eventTime = now(); // Usar la misma hora para todo el lote

        foreach ($records as $index => $record) {
            $rowNum = $index + 2; // +1 por el 0-index, +1 por la cabecera
            Log::info("Procesando fila $rowNum: ", $record);

            // 1. Validar datos del CSV
            $validator = Validator::make($record, [
                'asset_tag' => 'required|string',
                'organigram_member_email' => 'required|email',
                'assignment_date' => 'required|string', 
            ]);

            if ($validator->fails()) {
                $errors[] = "Fila $rowNum: Datos inválidos o faltantes. ({$validator->errors()->first()})";
                continue;
            }

            // --- Parseo de Fecha ---
            $parsedDate = null;
            $dateString = trim($record['assignment_date']);
            
            try { $parsedDate = Carbon::createFromFormat('Y-m-d', $dateString); } 
            catch (\Exception $e) {
                try { $parsedDate = Carbon::createFromFormat('d/m/Y', $dateString); } 
                catch (\Exception $e2) {
                    try { $parsedDate = Carbon::createFromFormat('m/d/Y', $dateString); } 
                    catch (\Exception $e3) {
                         $errors[] = "Fila $rowNum: El formato de fecha '{$dateString}' no es válido. Usa YYYY-MM-DD o DD/MM/YYYY.";
                         continue;
                    }
                }
            }
            
            // --- MODIFICACIÓN (Guardar Hora del Evento) ---
            $fullAssignmentDate = $parsedDate->setTime($eventTime->hour, $eventTime->minute, $eventTime->second);
            // --- FIN DE MODIFICACIÓN ---

            // 2. Validar Lógica de Negocio
            $asset = HardwareAsset::where('asset_tag', $record['asset_tag'])->first();
            if (!$asset) {
                $errors[] = "Fila $rowNum: No se encontró el activo con etiqueta '{$record['asset_tag']}'.";
                continue;
            }

            // --- MODIFICACIÓN (Lógica 1-a-N) ---
            $blockedStatuses = ['En Reparación', 'De Baja', 'En Mantenimiento'];
            if (in_array($asset->status, $blockedStatuses)) {
                $errors[] = "Fila $rowNum: El activo '{$record['asset_tag']}' no está disponible. Su estado actual es '{$asset->status}'. No se puede asignar.";
                continue;
            }
            // --- FIN DE MODIFICACIÓN ---

            $member = OrganigramMember::where('email', $record['organigram_member_email'])->first();
            if (!$member) {
                $errors[] = "Fila $rowNum: No se encontró al miembro con email '{$record['organigram_member_email']}'.";
                continue;
            }

            // 3. Procesar la asignación (usando transacción)
            try {
                DB::transaction(function () use ($asset, $member, $fullAssignmentDate) { 
                    $assignment = Assignment::create([
                        'hardware_asset_id' => $asset->id,
                        'organigram_member_id' => $member->id,
                        'assignment_date' => $fullAssignmentDate, // <-- Usar fecha/hora completa
                    ]);

                    $asset->logs()->create([
                        'user_id' => Auth::id(),
                        'action_type' => 'Asignación',
                        'notes' => 'Asignado a ' . $member->name,
                        'loggable_id' => $assignment->id,
                        'loggable_type' => Assignment::class,
                        'event_date' => $fullAssignmentDate, // <-- Usar fecha/hora completa
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