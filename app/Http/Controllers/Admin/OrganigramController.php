<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganigramMember;
use App\Models\Area;
use App\Models\OrganigramActivity;
use App\Models\OrganigramSkill;
use App\Models\OrganigramPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\OrganigramTrajectory;
use Illuminate\Support\Facades\Auth;
use League\Csv\Reader;
use League\Csv\Writer;
use League\Csv\CharsetConverter;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Statement;
use League\Csv\TabularDataReader;
use League\Csv\ByteSequence;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Mail\WelcomeNewUser;
use Illuminate\Support\Facades\Mail;


class OrganigramController extends Controller
{
    /**
     * Display a listing of the organigram members with optional filters.
     */
    /**
     * Display a listing of the organigram members with optional filters.
     * This version adds the full photo URL for client-side rendering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Fetch necessary data for filter dropdowns
        $areas = Area::orderBy('name')->get();
        $positions = OrganigramPosition::orderBy('name')->get();
        // Fetch all members as potential managers for the filter
        $managers = OrganigramMember::orderBy('name')->get(); 

        // Start building the query with eager loading for efficiency
        $query = OrganigramMember::with(['area', 'manager', 'position']); // Load relations needed for display

        // Apply server-side filters (primarily for initial load or if JS is disabled)
        // Note: The main filtering is now handled client-side by Alpine.js
        $selectedPosition = $request->input('position_id');
        if ($selectedPosition) {
            $query->where('position_id', $selectedPosition);
        }

        $selectedManager = $request->input('manager_id');
        if ($selectedManager) {
            if ($selectedManager === 'null') {
                $query->whereNull('manager_id');
            } else {
                $query->where('manager_id', $selectedManager);
            }
        }

        $selectedArea = $request->input('area_id');
        if ($selectedArea) {
            $query->where('area_id', $selectedArea);
        }

        // Apply server-side search (also primarily for initial load/non-JS)
        $searchQuery = $request->input('search');
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('email', 'like', '%' . $searchQuery . '%');
            });
        }

        // Execute the query to get the members
        // It's generally better to fetch ALL members for client-side filtering unless the dataset is huge.
        // If performance becomes an issue with thousands of members, you'd switch to server-side AJAX filtering.
        // For now, assuming client-side filtering is desired, we fetch all members matching basic filters.
        $membersQueryResults = $query->orderBy('name')->get(); 

        // --- ADD FULL PHOTO URL ---
        // Iterate over the results to add the full S3 URL needed by the frontend <img src="...">
        $members = $membersQueryResults->map(function ($member) {
            // Generate the full S3 URL if a path exists, otherwise set to null
            $member->profile_photo_path_url = $member->profile_photo_path 
                                              ? Storage::disk('s3')->url($member->profile_photo_path) 
                                              : null;
            
            // Ensure relations potentially used in Alpine templates are loaded. 
            // `with()` above should cover these, but `loadMissing` is safe.
            $member->loadMissing(['position', 'area', 'manager']); 

            return $member;
        });
        // --- END ADDING PHOTO URL ---
        
        // Pass the modified $members collection (now including `profile_photo_path_url`)
        // along with filter data to the Blade view.
        return view('admin.organigram.index', compact(
            'members',          // This collection goes into the `allMembers` Alpine variable
            'areas',            // For filter dropdown
            'positions',        // For filter dropdown
            'managers',         // For filter dropdown
            'selectedPosition', // To pre-select filter dropdowns
            'selectedManager',  // To pre-select filter dropdowns
            'selectedArea',     // To pre-select filter dropdowns
            'searchQuery'       // To pre-fill the search input
        ));
    }

    public function downloadTemplate()
    {
        $headers = [
            'name',
            'email',
            'cell_phone',
            'position_name',
            'area_name',
            'manager_email',
            'activities',
            'skills',
            'trajectory_title_1', 'trajectory_description_1', 'trajectory_start_date_1', 'trajectory_end_date_1',
            'trajectory_title_2', 'trajectory_description_2', 'trajectory_start_date_2', 'trajectory_end_date_2',
            'trajectory_title_3', 'trajectory_description_3', 'trajectory_start_date_3', 'trajectory_end_date_3',
            'trajectory_title_4', 'trajectory_description_4', 'trajectory_start_date_4', 'trajectory_end_date_4',
            'trajectory_title_5', 'trajectory_description_5', 'trajectory_start_date_5', 'trajectory_end_date_5',
        ];

        $data = [
            ['Juan Pérez', 'juan.perez@ejemplo.com', '5512345678', 'Gerente de Ventas', 'Ventas', 'jefe.ejemplo@ejemplo.com', 'Gestión de equipos, Estrategia de ventas', 'Liderazgo, Negociación', 'Gerente de Proyectos', 'Responsable de la planificación y ejecución.', '01-01-2018', '31-12-2020', '', '', '', '', '', '', '', '', '', '', '', ''],
        ];

        $csv = Writer::createFromString('');
        // Añadir el BOM de UTF-8 explícitamente
        $csv->setOutputBOM(Writer::BOM_UTF8); 
        $csv->insertOne($headers);
        $csv->insertAll($data);
        
        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8', // Asegurar el charset en la cabecera
            'Content-Disposition' => 'attachment; filename="organigram_template.csv"',
        ]);
    }
    
    /**
     * Importa miembros del organigrama desde un archivo CSV.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $csv->addStreamFilter('convert.iconv.UTF-8/UTF-8//IGNORE');

        $records = $csv->getRecords();

        $errors = [];
        $successCount = 0;

        DB::beginTransaction();
        try {
            foreach ($records as $index => $record) {
                Log::info("--- Procesando Fila CSV: " . ($index + 2) . " ---");
                
                // Los valores del registro ahora deberían ser UTF-8 gracias al filtro de stream.
                Log::info("Datos de la fila (después de filtro de codificación): ", $record);

                // Validación de campos requeridos, limpiando espacios
                $name = trim($record['name'] ?? '');
                $positionName = trim($record['position_name'] ?? '');
                $areaName = trim($record['area_name'] ?? '');

                if (empty($name) || empty($positionName) || empty($areaName)) {
                    $errors[] = "Fila " . ($index + 2) . ": Faltan campos requeridos (nombre, posición o área).";
                    Log::warning("Fila " . ($index + 2) . ": Campos requeridos vacíos.");
                    continue;
                }

                // Buscar y validar la posición
                $position = OrganigramPosition::where('name', $positionName)->first();
                if (!$position) {
                    $errors[] = "Fila " . ($index + 2) . ": La posición '" . $positionName . "' no existe. Las posiciones deben ser creadas manualmente.";
                    Log::warning("Fila " . ($index + 2) . ": Posición no encontrada: " . $positionName);
                    continue;
                }

                // Buscar el área
                $area = Area::where('name', $areaName)->first();
                if (!$area) {
                    $errors[] = "Fila " . ($index + 2) . ": El área '" . $areaName . "' no existe.";
                    Log::warning("Fila " . ($index + 2) . ": Área no encontrada: " . $areaName);
                    continue;
                }

                // Buscar el manager por email
                $managerId = null;
                $managerEmail = trim($record['manager_email'] ?? '');
                if (!empty($managerEmail)) {
                    $manager = OrganigramMember::where('email', $managerEmail)->first();
                    if ($manager) {
                        $managerId = $manager->id;
                    } else {
                        $errors[] = "Fila " . ($index + 2) . ": No se encontró un manager con el email '" . $managerEmail . "'. Se creará sin manager.";
                        Log::warning("Fila " . ($index + 2) . ": Manager no encontrado por email: " . $managerEmail);
                    }
                }

                // Crear o encontrar actividades
                $activityIds = [];
                $activitiesCsv = trim($record['activities'] ?? '');
                if (!empty($activitiesCsv)) {
                    $activities = explode(',', $activitiesCsv);
                    foreach ($activities as $activityName) {
                        $activityName = trim($activityName);
                        if (!empty($activityName)) {
                            $activity = OrganigramActivity::firstOrCreate(['name' => $activityName]);
                            $activityIds[] = $activity->id;
                        }
                    }
                }
                
                // Crear o encontrar habilidades
                $skillIds = [];
                $skillsCsv = trim($record['skills'] ?? '');
                if (!empty($skillsCsv)) {
                    $skills = explode(',', $skillsCsv);
                    foreach ($skills as $skillName) {
                        $skillName = trim($skillName);
                        if (!empty($skillName)) {
                            $skill = OrganigramSkill::firstOrCreate(['name' => $skillName]);
                            $skillIds[] = $skill->id;
                        }
                    }
                }
                
                // Lógica para actualizar o crear el miembro
                $member = null;
                $memberEmail = trim($record['email'] ?? '');
                if (!empty($memberEmail)) {
                    $member = OrganigramMember::firstOrNew(['email' => $memberEmail]);
                } else {
                    $errors[] = "Fila " . ($index + 2) . ": El email está vacío. Se requiere un email para identificar al miembro o crear uno nuevo.";
                    Log::warning("Fila " . ($index + 2) . ": Email del miembro vacío.");
                    continue; // Skip this record if email is empty and cannot identify
                }

                $member->fill([
                    'name' => $name,
                    'email' => !empty($memberEmail) ? $memberEmail : null,
                    'cell_phone' => trim($record['cell_phone'] ?? '') ?: null,
                    'position_id' => $position->id,
                    'area_id' => $area->id,
                    'manager_id' => $managerId,
                ])->save();
                Log::info("Miembro guardado/actualizado: ID " . $member->id . ", Nombre: " . $member->name);


                // Sincronizar actividades y habilidades
                if (!empty($activityIds)) {
                    $member->activities()->sync($activityIds);
                    Log::info("Actividades sincronizadas para miembro " . $member->id . ": ", $activityIds);
                } else {
                    $member->activities()->detach(); // Eliminar todas si la columna está vacía
                    Log::info("Actividades desvinculadas para miembro " . $member->id);
                }
                if (!empty($skillIds)) {
                    $member->skills()->sync($skillIds);
                    Log::info("Habilidades sincronizadas para miembro " . $member->id . ": ", $skillIds);
                } else {
                    $member->skills()->detach(); // Eliminar todas si la columna está vacía
                    Log::info("Habilidades desvinculadas para miembro " . $member->id);
                }

                // Lógica para la trayectoria (hasta 5 entradas)
                $member->trajectories()->delete(); // Limpiar la trayectoria anterior para evitar duplicados
                Log::info("Trayectorias anteriores eliminadas para miembro " . $member->id);

                for ($i = 1; $i <= 5; $i++) {
                    $titleKey = 'trajectory_title_' . $i;
                    $descriptionKey = 'trajectory_description_' . $i;
                    $startDateKey = 'trajectory_start_date_' . $i;
                    $endDateKey = 'trajectory_end_date_' . $i;

                    // Limpiar y verificar si el título de trayectoria existe
                    $trajectoryTitle = trim($record[$titleKey] ?? '');
                    
                    Log::info("Procesando trayectoria " . $i . " para miembro " . $member->id . ": Título crudo: '" . ($record[$titleKey] ?? 'N/A') . "', Título limpio: '" . $trajectoryTitle . "'");

                    if (!empty($trajectoryTitle)) {
                        $trajectoryDescription = trim($record[$descriptionKey] ?? '') ?: null;
                        $trajectoryStartDateRaw = trim($record[$startDateKey] ?? '');
                        $trajectoryEndDateRaw = trim($record[$endDateKey] ?? '') ?: null;

                        $startDate = null;
                        $endDate = null;

                        Log::info("Trayectoria " . $i . " - Fechas crudas: Inicio='" . $trajectoryStartDateRaw . "', Fin='" . $trajectoryEndDateRaw . "'");

                        // Validar y parsear las fechas en formato dd/mm/aaaa
                        try {
                            if (!empty($trajectoryStartDateRaw)) {
                                $startDate = Carbon::createFromFormat('d/m/Y', $trajectoryStartDateRaw)->format('Y-m-d');
                            }
                            if (!empty($trajectoryEndDateRaw)) {
                                $endDate = Carbon::createFromFormat('d/m/Y', $trajectoryEndDateRaw)->format('Y-m-d');
                            }
                            
                            $member->trajectories()->create([
                                'title' => $trajectoryTitle,
                                'description' => $trajectoryDescription,
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                            ]);
                            Log::info("Trayectoria " . $i . " creada para miembro " . $member->id . " con título '" . $trajectoryTitle . "' y fechas: " . $startDate . " a " . $endDate);

                        } catch (\Exception $e) {
                            $errors[] = "Fila " . ($index + 2) . ": Error en el formato de fecha de la trayectoria " . $i . " ('$trajectoryStartDateRaw' o '$trajectoryEndDateRaw'). Se esperaba 'dd/mm/aaaa'. Detalles: " . $e->getMessage();
                            Log::error("Error al parsear fecha de trayectoria en fila " . ($index + 2) . ", trayectoria " . $i . ": " . $e->getMessage());
                            // Continúa con la siguiente trayectoria para este miembro, no salta al siguiente miembro.
                            continue; 
                        }
                    } else {
                        Log::info("Trayectoria " . $i . " no procesada para miembro " . $member->id . " porque el título está vacío.");
                    }
                }

                $successCount++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en importCsv (transacción): " . $e->getMessage() . " en archivo " . $e->getFile() . " linea " . $e->getLine());
            return redirect()->route('admin.organigram.create')->with('error', 'Ocurrió un error inesperado al procesar el archivo. Ningún miembro fue creado. Detalles: ' . $e->getMessage());
        }

        if (empty($errors)) {
            return redirect()->route('admin.organigram.index')->with('success', 'Se han creado/actualizado ' . $successCount . ' miembros exitosamente.');
        } else {
            $errorMessage = "Se crearon/actualizaron $successCount miembros, pero ocurrieron errores en las siguientes filas: <br>" . implode('<br>', $errors) . "<br>Por favor, revisa el archivo y los errores específicos.";
            return redirect()->route('admin.organigram.index')->with('warning', $errorMessage);
        }
    }

    /**
     * Helper para construir la jerarquía (recursivo)
     */
    protected function buildHierarchy($member, $allMembers)
    {
        $member->children = $allMembers->where('manager_id', $member->id)->map(function ($child) use ($allMembers) {
            return $this->buildHierarchy($child, $allMembers);
        })->values();

        return $member;
    }

    /**
     * Show the form for creating a new organigram member.
     */
    public function create()
    {
        $areas = Area::orderBy('name')->get();
        $managers = OrganigramMember::with(['area', 'position'])->orderBy('name')->get();
        $activities = OrganigramActivity::orderBy('name')->get();
        $skills = OrganigramSkill::orderBy('name')->get();
        $positions = OrganigramPosition::orderBy('hierarchy_level')->orderBy('name')->get();

        return view('admin.organigram.create', compact('areas', 'managers', 'activities', 'skills', 'positions'));
    }

    /**
     * Store a newly created organigram member in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:organigram_members,email',
            'cell_phone' => 'nullable|string|max:20',
            'position_id' => 'required|exists:organigram_positions,id',
            'area_id' => 'required|exists:areas,id',
            'manager_id' => 'nullable|exists:organigram_members,id',
            'profile_photo' => 'nullable|image|max:2048',
            'activities_ids' => 'nullable|array',
            'activities_ids.*' => 'exists:organigram_activities,id',
            'skills_ids' => 'nullable|array',
            'skills_ids.*' => 'exists:organigram_skills,id',
            'trajectories' => 'nullable|array',
            'trajectories.*.title' => 'required_with:trajectories.*.start_date|string|max:255',
            'trajectories.*.description' => 'nullable|string|max:1000',
            'trajectories.*.start_date' => 'nullable|date',
            'trajectories.*.end_date' => 'nullable|date|after_or_equal:trajectories.*.start_date',
            'create_user_account' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $newUserId = null;

        // --- LÓGICA ACTUALIZADA PARA CREACIÓN Y VINCULACIÓN DE USUARIO ---
        if ($request->boolean('create_user_account')) {
            $existingUser = User::where('email', $validatedData['email'])->first();

            if ($existingUser) {
                // Si el usuario ya existe, simplemente obtenemos su ID para vincularlo
                $newUserId = $existingUser->id;
            } else {
                // Si el usuario no existe, lo creamos
                $temporaryPassword = Str::random(12);
                $newUser = User::create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($temporaryPassword),
                    'area_id' => $validatedData['area_id'],
                    'position' => OrganigramPosition::find($validatedData['position_id'])->name,
                ]);

                $newUserId = $newUser->id;

                // --- LÓGICA DE ENVÍO DE CORREO INTEGRADA ---
                try {
                    Mail::to($newUser->email)->send(new WelcomeNewUser($newUser, $temporaryPassword));
                } catch (\Exception $e) {
                    // Si el correo falla, no detenemos el proceso, pero lo registramos
                    Log::error("Error al enviar correo de bienvenida desde Organigrama a {$newUser->email}: " . $e->getMessage());
                }
                // --- FIN DE LA LÓGICA DE CORREO ---
            }
        }

        $path = null;
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('organigram-photos', 's3');
        }

        // Creamos el miembro del organigrama
        $member = OrganigramMember::create([
            'user_id' => $newUserId, // <-- Aquí se guarda el vínculo con el usuario
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'cell_phone' => $validatedData['cell_phone'],
            'position_id' => $validatedData['position_id'],
            'area_id' => $validatedData['area_id'],
            'manager_id' => $validatedData['manager_id'],
            'profile_photo_path' => $path,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Sincronizamos las relaciones
        $member->activities()->sync($request->input('activities_ids', []));
        $member->skills()->sync($request->input('skills_ids', []));

        if ($request->has('trajectories')) {
            foreach ($request->trajectories as $trajectoryData) {
                if (!empty($trajectoryData['title'])) {
                    unset($trajectoryData['id']);
                    $member->trajectories()->create($trajectoryData);
                }
            }
        }

        return redirect()->route('admin.organigram.index')->with('success', 'Miembro del organigrama creado exitosamente.');
    }

    /**
     * Show the form for editing the specified organigram member.
     */
    public function edit(OrganigramMember $organigramMember)
    {
        $areas = Area::orderBy('name')->get();
        $managers = OrganigramMember::where('id', '!=', $organigramMember->id)
                                    ->with(['area', 'position'])
                                    ->orderBy('name')->get();
        $activities = OrganigramActivity::orderBy('name')->get();
        $skills = OrganigramSkill::orderBy('name')->get();
        $positions = OrganigramPosition::orderBy('hierarchy_level')->orderBy('name')->get();

        $organigramMember->load(['activities', 'skills', 'trajectories', 'position']);

        // Mapear las trayectorias para formatear las fechas a YYYY-MM-DD para el input type="date"
        // y asegurar que se conviertan a un array de arrays simples para json_encode
        $trajectories = $organigramMember->trajectories->map(function ($trajectory) {
            return [
                'id' => $trajectory->id,
                'title' => $trajectory->title,
                'description' => $trajectory->description,
                'start_date' => optional($trajectory->start_date)->format('Y-m-d'), // Formato YYYY-MM-DD
                'end_date' => optional($trajectory->end_date)->format('Y-m-d'),     // Formato YYYY-MM-DD
                // Incluir otros campos si son necesarios en el frontend, pero formatéalos si son fechas
                'created_at' => optional($trajectory->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($trajectory->updated_at)->format('Y-m-d H:i:s'),
                'organigram_member_id' => $trajectory->organigram_member_id,
            ];
        })->toArray(); // ¡CAMBIO CLAVE AQUÍ! Convertir a array de arrays PHP

        // === INICIO DE DIAGNÓSTICO ===
        Log::info('Trayectorias formateadas enviadas a la vista de edición (array final):', $trajectories);
        // === FIN DE DIAGNÓSTICO ===

        $memberActivitiesIds = $organigramMember->activities->pluck('id')->toArray();
        $memberSkillsIds = $organigramMember->skills->pluck('id')->toArray();

        // Pasar la variable $trajectories formateada a la vista
        return view('admin.organigram.edit', compact('organigramMember', 'areas', 'managers', 'activities', 'skills', 'memberActivitiesIds', 'memberSkillsIds', 'positions', 'trajectories'));
    }

    /**
     * Update the specified organigram member in storage.
     */
    public function update(Request $request, OrganigramMember $organigramMember)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'cell_phone' => 'nullable|string|max:20',
            'position_id' => 'required|exists:organigram_positions,id',
            'area_id' => 'required|exists:areas,id',
            'manager_id' => [
                'nullable',
                'exists:organigram_members,id',
                Rule::notIn([$organigramMember->id]),
                function ($attribute, $value, $fail) use ($organigramMember) {
                    if ($value && $this->isDescendant($organigramMember, OrganigramMember::find($value))) {
                        $fail('No puedes asignar un subordinado como su propio manager.');
                    }
                },
            ],
            'profile_photo' => 'nullable|image|max:2048',
            'remove_profile_photo' => 'boolean',
            'activities_ids' => 'nullable|array',
            'activities_ids.*' => 'exists:organigram_activities,id',
            'skills_ids' => 'nullable|array',
            'skills_ids.*' => 'exists:organigram_skills,id',
            'trajectories' => 'nullable|array',
            'trajectories.*.id' => 'nullable|exists:organigram_trajectories,id',
            'trajectories.*.title' => 'required_with:trajectories.*.start_date|string|max:255',
            'trajectories.*.description' => 'nullable|string|max:1000',
            'trajectories.*.start_date' => 'nullable|date',
            'trajectories.*.end_date' => 'nullable|date|after_or_equal:trajectories.*.start_date',
            'create_user_account' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->boolean('create_user_account') && is_null($organigramMember->user_id)) {
            $existingUser = User::where('email', $validatedData['email'])->first();

            if ($existingUser) {
                $organigramMember->user_id = $existingUser->id;
            } else {
                $temporaryPassword = Str::random(12);
                $newUser = User::create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($temporaryPassword),
                    'area_id' => $validatedData['area_id'],
                    'position' => \App\Models\OrganigramPosition::find($validatedData['position_id'])->name,
                ]);

                $organigramMember->user_id = $newUser->id;

                try {
                    Mail::to($newUser->email)->send(new WelcomeNewUser($newUser, $temporaryPassword));
                } catch (\Exception $e) {
                    Log::error("Error al enviar correo de bienvenida desde Organigrama (Update) a {$newUser->email}: " . $e->getMessage());
                }
            }
        }        

        if ($request->hasFile('profile_photo')) {
            if ($organigramMember->profile_photo_path) {
                Storage::disk('s3')->delete($organigramMember->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('organigram-photos', 's3');
            $organigramMember->profile_photo_path = $path;
        } elseif ($request->input('remove_profile_photo')) {
            if ($organigramMember->profile_photo_path) {
                Storage::disk('s3')->delete($organigramMember->profile_photo_path);
                $organigramMember->profile_photo_path = null;
            }
        }

        $organigramMember->fill($request->except(['profile_photo', 'remove_profile_photo', 'activities_ids', 'skills_ids', 'trajectories', 'create_user_account', 'is_active']));
        $organigramMember->is_active = $request->boolean('is_active');        
        $organigramMember->save();

        $organigramMember->activities()->sync($request->input('activities_ids', []));
        $organigramMember->skills()->sync($request->input('skills_ids', []));

        $existingTrajectoryIds = $organigramMember->trajectories->pluck('id')->toArray();
        $updatedTrajectoryIds = [];

        if ($request->has('trajectories')) {
            foreach ($request->trajectories as $trajectoryData) {
                if (isset($trajectoryData['id']) && $trajectoryData['id'] != null) {
                    $organigramMember->trajectories()->where('id', $trajectoryData['id'])->update($trajectoryData);
                    $updatedTrajectoryIds[] = $trajectoryData['id'];
                } else {
                    unset($trajectoryData['id']);
                    $newTrajectory = $organigramMember->trajectories()->create($trajectoryData);
                    $updatedTrajectoryIds[] = $newTrajectory->id;
                }
            }
        }
        $trajectoriesToDelete = array_diff($existingTrajectoryIds, $updatedTrajectoryIds);
        if (!empty($trajectoriesToDelete)) {
            $organigramMember->trajectories()->whereIn('id', $trajectoriesToDelete)->delete();
        }

        return redirect()->route('admin.organigram.index')->with('success', 'Miembro del organigrama actualizado exitosamente.');
    }

    /**
     * Helper para prevenir ciclos en la jerarquía (ej. A es manager de B, B no puede ser manager de A)
     */
    protected function isDescendant($potentialManager, $potentialSubordinate)
    {
        if (!$potentialSubordinate) {
            return false;
        }

        $current = $potentialSubordinate;
        while ($current) {
            if ($current->id === $potentialManager->id) {
                return true;
            }
            $current = $current->manager;
        }
        return false;
    }

    /**
     * Remove the specified organigram member from storage.
     */
    public function destroy(OrganigramMember $organigramMember)
    {
        if ($organigramMember->profile_photo_path) {
            Storage::disk('s3')->delete($organigramMember->profile_photo_path);
        }

        $organigramMember->delete();

        return redirect()->route('admin.organigram.index')->with('success', 'Miembro del organigrama eliminado exitosamente.');
    }

    /**
     * Show the form for managing Activities.
     */
    public function activitiesIndex()
    {
        $activities = OrganigramActivity::orderBy('name')->get();
        return view('admin.organigram.activities.index', compact('activities'));
    }

    /**
     * Store a new Activity.
     */
    public function activitiesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organigram_activities,name',
            'description' => 'nullable|string|max:1000',
        ]);
        OrganigramActivity::create($request->all());
        return redirect()->route('admin.organigram.activities.index')->with('success', 'Actividad creada.');
    }

    /**
     * Update an Activity.
     */
    public function activitiesUpdate(Request $request, OrganigramActivity $activity)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('organigram_activities', 'name')->ignore($activity->id)],
            'description' => 'nullable|string|max:1000',
        ]);
        $activity->update($request->all());
        return redirect()->route('admin.organigram.activities.index')->with('success', 'Actividad actualizada.');
    }

    /**
     * Delete an Activity.
     */
    public function activitiesDestroy(OrganigramActivity $activity)
    {
        $activity->delete();
        return redirect()->route('admin.organigram.activities.index')->with('success', 'Actividad eliminada.');
    }

    /**
     * Show the form for managing Skills.
     */
    public function skillsIndex()
    {
        $skills = OrganigramSkill::orderBy('name')->get();
        return view('admin.organigram.skills.index', compact('skills'));
    }

    /**
     * Store a new Skill.
     */
    public function skillsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organigram_skills,name',
            'description' => 'nullable|string|max:1000',
        ]);
        OrganigramSkill::create($request->all());
        return redirect()->route('admin.organigram.skills.index')->with('success', 'Habilidad creada.');
    }

    /**
     * Update a Skill.
     */
    public function skillsUpdate(Request $request, OrganigramSkill $skill)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('organigram_skills', 'name')->ignore($skill->id)],
            'description' => 'nullable|string|max:1000',
        ]);
        $skill->update($request->all());
        return redirect()->route('admin.organigram.skills.index')->with('success', 'Habilidad actualizada.');
    }

    /**
     * Delete a Skill.
     */
    public function skillsDestroy(OrganigramSkill $skill)
    {
        $skill->delete();
        return redirect()->route('admin.organigram.skills.index')->with('success', 'Habilidad eliminada.');
    }

    /**
     * Muestra la vista para el organigrama interactivo.
     * @return \Illuminate\View\View
     */
    public function interactiveOrganigram()
    {
        return view('admin.organigram.interactive');
    }

    /**
     * Genera los datos del organigrama en formato JSON, incluyendo áreas y proxies.
     * Esta es la vista "completa".
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInteractiveOrganigramData()
    {
        $user = Auth::user();

        // Define las áreas permitidas para el cliente.
        $allowedAreaNames = [
            'Customer Service',
            'Comercial',
            'Operaciones',
            'Desarrollo y TI',
            'Finanzas',
            'Recursos Humanos',
            'Tráfico',
            'Brokerage',
            'Diseño y MKT',
            'Almacén',
        ];

        if ($user && $user->is_client) {
            $allMembers = OrganigramMember::with(['area', 'manager.position', 'position', 'activities', 'skills', 'trajectories'])
            ->where('is_active', true)
            ->get();
            $areas = Area::whereIn('name', $allowedAreaNames)->get();

            $flatNodes = [];

            // 1. Nodo Raíz principal para la vista de cliente
            $flatNodes[] = [
                'id' => 'org_root',
                'pid' => null,
                'name' => 'MINMER GLOBAL',
                'title' => 'Organigrama Principal',
                'type' => 'root',
                'img' => asset('images/LogoAzul.png'),
            ];

            // Nodo del Consejo Directivo
            $flatNodes[] = [
                'id' => 'consejo_directivo',
                'pid' => 'org_root',
                'name' => 'Consejo Directivo',
                'title' => 'Directiva',
                'type' => 'council',
                'img' => Storage::disk('s3')->url('logoNode1.png'),
            ];

            // 2. Nodos de Áreas
            foreach ($areas as $area) {
                $flatNodes[] = [
                    'id' => 'area_' . $area->id,
                    'pid' => 'consejo_directivo',
                    'name' => $area->name,
                    'title' => 'Área',
                    'type' => 'area',
                    'img' => $area->icon_path ? Storage::disk('s3')->url($area->icon_path) : null,
                    'description' => $area->description,
                ];
            }

            // Lógica para mánagers transversales y proxies (clientes)
            $proxiesToCreate = [];
            foreach ($allMembers as $member) {
                // Solo procesar los miembros de las áreas permitidas.
                if (!in_array($member->area->name, $allowedAreaNames)) {
                    continue;
                }

                if ($member->manager && $member->area_id !== $member->manager->area_id) {
                    $manager = $member->manager;
                    $targetAreaId = $member->area_id;

                    $proxyKey = 'proxy_' . $manager->id . '_area_' . $targetAreaId;

                    if (!isset($proxiesToCreate[$proxyKey])) {
                        $proxiesToCreate[$proxyKey] = [
                            'id' => $proxyKey,
                            'pid' => 'area_' . $targetAreaId,
                            'original_id' => (string)$manager->id,
                            'name' => $manager->name,
                            'title' => $manager->position->name ?? 'Sin Posición',
                            'img' => $manager->profile_photo_path ? Storage::disk('s3')->url($manager->profile_photo_path) : null,
                            'type' => 'member',
                            'is_proxy' => true,
                        ];
                    }
                }
            }

            foreach ($proxiesToCreate as $proxyNode) {
                $flatNodes[] = $proxyNode;
            }

            // 3. Nodos de Miembros
            foreach ($allMembers as $member) {
                if (!in_array($member->area->name, $allowedAreaNames)) {
                    continue;
                }

                $parentId = null;
                if ($member->manager) {
                    if ($member->area_id !== $member->manager->area_id) {
                        $parentId = 'proxy_' . $member->manager_id . '_area_' . $member->area_id;
                    } else {
                        $parentId = (string)$member->manager_id;
                    }
                } else {
                    $parentId = 'area_' . $member->area_id;
                }

                $flatNodes[] = [
                    'id' => (string)$member->id,
                    'pid' => $parentId,
                    'name' => $member->name,
                    'title' => $member->position->name ?? 'Sin Posición',
                    'img' => $member->profile_photo_path ? Storage::disk('s3')->url($member->profile_photo_path) : null,
                    'type' => 'member',
                    'is_proxy' => false,
                    'className' => 'level-' . ($member->position->hierarchy_level ?? 'unknown'),
                    'full_details' => [
                        'name' => $member->name,
                        'email' => $member->email,
                        'cell_phone' => $member->cell_phone,
                        'position_name' => $member->position->name ?? 'N/A',
                        'position_description' => $member->position->description ?? 'N/A',
                        'hierarchy_level' => $member->position->hierarchy_level ?? null,
                        'area_name' => $member->area->name ?? 'N/A',
                        'manager_name' => $member->manager->name ?? 'N/A',
                        'manager_id' => $member->manager_id,
                        'profile_photo_path' => $member->profile_photo_path ? Storage::disk('s3')->url($member->profile_photo_path) : null,
                        'activities' => $member->activities->map(fn($a) => ['id' => $a->id, 'name' => $a->name]),
                        'skills' => $member->skills->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
                        'trajectories' => $member->trajectories->map(fn($t) => [
                            'id' => $t->id, 'title' => $t->title, 'description' => $t->description,
                            'start_date' => optional($t->start_date)->format('Y-m-d'), 'end_date' => optional($t->end_date)->format('Y-m-d'),
                        ]),
                    ]
                ];
            }

            $nestedTree = $this->buildNestedTree($flatNodes);
            return response()->json($nestedTree[0] ?? null);
        }

        // Código para usuarios internos (no clientes)
        $members = OrganigramMember::with(['area', 'manager.position', 'position', 'activities', 'skills', 'trajectories'])
        ->where('is_active', true)
        ->get();
        $areas = Area::all();

        $flatNodes = [];

        // 1. Nodo Raíz
        $flatNodes[] = [
            'id' => 'org_root',
            'pid' => null,
            'name' => 'MINMER GLOBAL',
            'title' => 'Organigrama Principal',
            'type' => 'root',
            'img' => asset('images/LogoAzul.png'),
        ];

        $flatNodes[] = [
            'id' => 'consejo_directivo',
            'pid' => 'org_root',
            'name' => 'Consejo Directivo',
            'title' => 'Directiva',
            'type' => 'council',
            'img' => Storage::disk('s3')->url('logoNode1.png'),
        ];

        // 2. Nodos de Áreas
        foreach ($areas as $area) {
            $flatNodes[] = [
                'id' => 'area_' . $area->id,
                'pid' => 'consejo_directivo',
                'name' => $area->name,
                'title' => 'Área',
                'type' => 'area',
                'img' => $area->icon_path ? Storage::disk('s3')->url($area->icon_path) : null,
                'description' => $area->description,
            ];
        }

        // Lógica para mánagers transversales y proxies (usuarios internos)
        $proxiesToCreate = [];
        foreach ($members as $member) {
            if ($member->manager && $member->area_id !== $member->manager->area_id) {
                $manager = $member->manager;
                $targetAreaId = $member->area_id;

                $proxyKey = 'proxy_' . $manager->id . '_area_' . $targetAreaId;

                if (!isset($proxiesToCreate[$proxyKey])) {
                    $proxiesToCreate[$proxyKey] = [
                        'id' => $proxyKey,
                        'pid' => 'area_' . $targetAreaId,
                        'original_id' => (string)$manager->id,
                        'name' => $manager->name,
                        'title' => $manager->position->name ?? 'Sin Posición',
                        'img' => $manager->profile_photo_path ? Storage::disk('s3')->url($manager->profile_photo_path) : null,
                        'type' => 'member',
                        'is_proxy' => true,
                    ];
                }
            }
        }

        foreach ($proxiesToCreate as $proxyNode) {
            $flatNodes[] = $proxyNode;
        }

        // 5. Nodos de Miembros
        foreach ($members as $member) {
            $parentId = null;

            if ($member->manager) {
                if ($member->area_id !== $member->manager->area_id) {
                    $parentId = 'proxy_' . $member->manager_id . '_area_' . $member->area_id;
                } else {
                    $parentId = (string)$member->manager_id;
                }
            } else {
                $parentId = 'area_' . $member->area_id;
            }

            $flatNodes[] = [
                'id' => (string)$member->id,
                'pid' => $parentId,
                'name' => $member->name,
                'title' => $member->position->name ?? 'Sin Posición',
                'img' => $member->profile_photo_path ? Storage::disk('s3')->url($member->profile_photo_path) : null,
                'type' => 'member',
                'is_proxy' => false,
                'full_details' => [
                    'name' => $member->name,
                    'email' => $member->email,
                    'cell_phone' => $member->cell_phone,
                    'position_name' => $member->position->name ?? 'N/A',
                    'position_description' => $member->position->description ?? 'N/A',
                    'area_name' => $member->area->name ?? 'N/A',
                    'manager_name' => $member->manager->name ?? 'N/A',
                    'manager_id' => $member->manager_id,
                    'profile_photo_path' => $member->profile_photo_path ? Storage::disk('s3')->url($member->profile_photo_path) : null,
                    'activities' => $member->activities->map(fn($a) => ['id' => $a->id, 'name' => $a->name]),
                    'skills' => $member->skills->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
                    'trajectories' => $member->trajectories->map(fn($t) => [
                        'id' => $t->id, 'title' => $t->title, 'description' => $t->description,
                        'start_date' => optional($t->start_date)->format('Y-m-d'), 'end_date' => optional($t->end_date)->format('Y-m-d'),
                    ]),
                ]
            ];
        }

        $nestedTree = $this->buildNestedTree($flatNodes);
        return response()->json($nestedTree[0] ?? null);
    }

    /**
     * NUEVA FUNCIÓN: Genera los datos del organigrama en formato JSON, SOLO con miembros reales.
     * Esta es la vista "sin áreas".
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInteractiveOrganigramDataWithoutAreas()
    {
        $user = Auth::user();

        if ($user && $user->is_client) {
            // Define las áreas permitidas para el cliente.
            $allowedAreaNames = [
                // 'Customer Service',
                'Comercial',
                'Operaciones',
                'Desarrollo y TI',
                'Finanzas',
            ];

            // Obtener los IDs de las áreas permitidas
            $allowedAreaIds = Area::whereIn('name', $allowedAreaNames)->pluck('id');

            if ($allowedAreaIds->isEmpty()) {
                return response()->json(null);
            }

            $allMembers = OrganigramMember::with(['area', 'manager.position', 'position'])
            ->where('is_active', true)
            ->get();
            
            // Encontrar a los "jefes" de las áreas permitidas.
            // Un jefe es un miembro de un área permitida que no tiene manager, o cuyo manager no está en la misma área.
            $headsOfService = $allMembers->whereIn('area_id', $allowedAreaIds)->filter(function ($member) {
                return is_null($member->manager_id) || ($member->manager && $member->manager->area_id != $member->area_id);
            });

            if ($headsOfService->isEmpty()) {
                return response()->json(null);
            }

            $membersInHierarchy = collect();
            foreach ($headsOfService as $headOfService) {
                $membersInHierarchy = $membersInHierarchy->merge($this->getDescendants($headOfService, $allMembers));
            }
            $membersInHierarchy = $membersInHierarchy->unique('id');

            $flatNodesForMembersOnly = [];

            // 1. Nodo Raíz principal (único para toda la jerarquía de clientes)
            $flatNodesForMembersOnly[] = [
                'id' => 'org_root',
                'pid' => null,
                'name' => 'MINMER GLOBAL',
                'title' => 'Organigrama Principal',
                'type' => 'root',
                'img' => asset('images/LogoAzul.png'),
            ];

            $flatNodesForMembersOnly[] = [
                'id' => 'consejo_directivo',
                'pid' => 'org_root', // El padre es el nodo raíz
                'name' => 'Consejo Directivo',
                'title' => 'Directiva',
                'type' => 'council',
                'img' => Storage::disk('s3')->url('logoNode1.png'), // Usar la imagen de S3
            ];            

            // 2. Nodos de Miembros (Re-parenting directo)
            foreach ($membersInHierarchy as $member) {
                $parentId = null;

                // Si es un jefe de servicio, su padre es el nodo raíz principal.
                if ($headsOfService->contains('id', $member->id)) {
                    $parentId = 'consejo_directivo';
                } else {
                    // Si no es un jefe, su padre es su manager real.
                    $parentId = (string)$member->manager_id;
                }

                $flatNodesForMembersOnly[] = [
                    'id' => (string)$member->id,
                    'pid' => $parentId,
                    'name' => $member->name,
                    'title' => $member->position->name ?? 'Sin Posición',
                    'img' => $member->profile_photo_path ? Storage::disk('s3')->url($member->profile_photo_path) : null,
                    'type' => 'member',
                    'is_proxy' => false,
                    'className' => 'level-' . ($member->position->hierarchy_level ?? 'unknown'),
                    'full_details' => [
                        'name' => $member->name,
                        'email' => $member->email,
                        'cell_phone' => $member->cell_phone,
                        'position_name' => $member->position->name ?? 'N/A',
                        'position_description' => $member->position->description ?? 'N/A',
                        'hierarchy_level' => $member->position->hierarchy_level ?? null,
                        'area_name' => $member->area->name ?? 'N/A',
                        'manager_name' => $member->manager->name ?? 'N/A',
                        'manager_id' => $member->manager_id,
                        'profile_photo_path' => $member->profile_photo_path ? Storage::disk('s3')->url($member->profile_photo_path) : null,
                        'activities' => $member->activities->map(fn($a) => ['id' => $a->id, 'name' => $a->name]),
                        'skills' => $member->skills->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
                        'trajectories' => $member->trajectories->map(fn($t) => [
                            'id' => $t->id, 'title' => $t->title, 'description' => $t->description,
                            'start_date' => optional($t->start_date)->format('Y-m-d'), 'end_date' => optional($t->end_date)->format('Y-m-d'),
                        ]),
                    ]
                ];
            }

            $nestedTree = $this->buildNestedTree($flatNodesForMembersOnly);
            return response()->json($nestedTree[0] ?? null);
        }
        $members = OrganigramMember::with(['area', 'manager.position', 'position', 'activities', 'skills', 'trajectories'])
        ->where('is_active', true)
        ->get();

        $flatNodesForMembersOnly = [];

        // 1. Nodo Raíz
        $flatNodesForMembersOnly[] = [
            'id' => 'org_root',
            'pid' => null,
            'name' => 'MINMER GLOBAL',
            'title' => 'Organigrama Principal',
            'type' => 'root',
            'img' => asset('images/LogoAzul.png'),
        ];

        $flatNodesForMembersOnly[] = [
            'id' => 'consejo_directivo',
            'pid' => 'org_root', // El padre es el nodo raíz
            'name' => 'Consejo Directivo',
            'title' => 'Directiva',
            'type' => 'council',
            'img' => Storage::disk('s3')->url('logoNode1.png'), // Usar la imagen de S3
        ];        

        // 2. Nodos de Miembros (Re-parenting directo)
        foreach ($members as $member) {
            $parentId = null;

            // Si el miembro tiene un manager real
            if ($member->manager_id) {
                $parentId = (string)$member->manager_id;
            } else {
                // Si no tiene manager, se asigna al nodo raíz
                $parentId = 'consejo_directivo';
            }

            $flatNodesForMembersOnly[] = [
                'id' => (string)$member->id,
                'pid' => $parentId,
                'name' => $member->name,
                'title' => $member->position->name ?? 'Sin Posición',
                'img' => $member->profile_photo_path ? Storage::disk('s3')->url($member->profile_photo_path) : null,
                'type' => 'member',
                'is_proxy' => false, // Siempre false en esta vista
                'full_details' => [
                    'name' => $member->name,
                    'email' => $member->email,
                    'cell_phone' => $member->cell_phone,
                    'position_name' => $member->position->name ?? 'N/A',
                    'position_description' => $member->position->description ?? 'N/A',
                    'area_name' => $member->area->name ?? 'N/A',
                    'manager_name' => $member->manager->name ?? 'N/A',
                    'manager_id' => $member->manager_id,
                    'profile_photo_path' => $member->profile_photo_path ? Storage::disk('s3')->url($member->profile_photo_path) : null,
                    'activities' => $member->activities->map(fn($a) => ['id' => $a->id, 'name' => $a->name]),
                    'skills' => $member->skills->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
                    'trajectories' => $member->trajectories->map(fn($t) => [
                        'id' => $t->id, 'title' => $t->title, 'description' => $t->description,
                        'start_date' => optional($t->start_date)->format('Y-m-d'), 'end_date' => optional($t->end_date)->format('Y-m-d'),
                    ]),
                ]
            ];
        }

        $nestedTree = $this->buildNestedTree($flatNodesForMembersOnly);
        return response()->json($nestedTree[0] ?? null);
    }

    /**
     * Convierte una lista plana de nodos en un árbol anidado.
     * Esta función ha sido optimizada para asegurar la correcta anidación.
     */
    private function buildNestedTree(array &$elements, $parentId = null, int $depth = 0) {
        $branch = [];
        $indexedElements = [];

        // Indexar elementos por ID para acceso rápido
        foreach ($elements as $element) {
            $indexedElements[(string)$element['id']] = $element;
        }

        foreach ($elements as &$element) {
            if ((string)$element['pid'] === (string)$parentId) {
                // Si el elemento ya tiene una clave 'children', asegúrate de que sea un array
                if (!isset($element['children']) || !is_array($element['children'])) {
                    $element['children'] = [];
                }

                // =====================================================================
                // MODIFICACIÓN CLAVE AQUÍ: Marcar como colapsado si no es el nodo raíz
                // =====================================================================
                // Si es el nodo raíz ('org_root'), NO lo colapses.
                // Si NO es el nodo raíz (es un hijo o cualquier otro nivel), colápsalo.
                if ((string)$element['id'] !== 'org_root') {
                    $element['collapsed'] = true;
                } else {
                    $element['collapsed'] = false; // Asegurar que la raíz siempre esté expandida
                }
                // =====================================================================

                $children = $this->buildNestedTree($elements, $element['id'], $depth + 1);

                if (!empty($children)) {
                    $element['children'] = $children;
                } else {
                    if (isset($element['children'])) {
                        unset($element['children']);
                    }
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    protected function getDescendants($member, $allMembers)
    {
        $descendants = collect([$member]);
        $children = $allMembers->where('manager_id', $member->id);

        foreach ($children as $child) {
            $descendants = $descendants->merge($this->getDescendants($child, $allMembers));
        }

        return $descendants;
    }

    /**
     * Remove multiple specified organigram members from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroy(Request $request)
    {
        // 1. Validate incoming IDs
        $validatedData = $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:organigram_members,id',
        ]);

        $idsToDelete = $validatedData['selected_ids'];
        $deleteCount = 0; // Initialize count

        // --- HANDLING RELATED ASSIGNMENTS ---
        try {
             \App\Models\Assignment::whereIn('organigram_member_id', $idsToDelete)
                                   ->update(['organigram_member_id' => null]);
            Log::info('Assignments successfully disassociated (set to NULL) for members being deleted.', ['member_ids' => $idsToDelete]);

        } catch (\Illuminate\Database\QueryException $e) {
             // ... (error handling as before) ...
             Log::error("Error disassociating assignments during bulk member deletion: " . $e->getMessage(), [ /* ... */ ]);
             $errorMessage = 'No se pudieron eliminar los miembros. ';
             // ... (error message logic) ...
             return redirect()->route('admin.organigram.index')->with('error', $errorMessage);
        } catch (\Exception $e) {
             // ... (error handling as before) ...
            Log::error("Unexpected error during assignment handling in bulk member deletion: " . $e->getMessage(), ['member_ids' => $idsToDelete]);
            return redirect()->route('admin.organigram.index')->with('error', 'Ocurrió un error inesperado al gestionar las asignaciones.');
        }

        // 2. Delete S3 profile photos
        $membersToDelete = OrganigramMember::whereIn('id', $idsToDelete)->get(['id', 'profile_photo_path']);
        foreach ($membersToDelete as $member) {
            // ... (photo deletion logic) ...
             if ($member->profile_photo_path) {
                try {
                    Storage::disk('s3')->delete($member->profile_photo_path);
                } catch (\Exception $e) {
                    Log::error("Error deleting S3 photo for member ID {$member->id} during bulk delete: " . $e->getMessage());
                }
            }
        }

        // 3. Delete the members
        try {
            $deleteCount = OrganigramMember::whereIn('id', $idsToDelete)->delete();
        } catch (\Exception $e) {
             // ... (error handling) ...
             Log::error("Error occurred during the final member deletion step: " . $e->getMessage(), ['member_ids' => $idsToDelete]);
             return redirect()->route('admin.organigram.index')->with('error', 'Ocurrió un error al intentar eliminar los miembros.');
        }

        // --- BROWSER NOTIFICATION LOGIC ---
        $notificationMessage = $deleteCount . ' miembro(s) eliminado(s) exitosamente.';
        $successMessage = $notificationMessage . ' Las asignaciones asociadas fueron desvinculadas.';

        // Redirect with both standard flash and browser notification data
        return redirect()->route('admin.organigram.index')
                         ->with('success', $successMessage) // Standard flash message
                         ->with('browser_notification', $notificationMessage); // Message for browser notification
        // --- END BROWSER NOTIFICATION LOGIC ---
    }
}