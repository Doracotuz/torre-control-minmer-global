<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganigramMember;
use App\Models\Area; // Para seleccionar el área del miembro
use App\Models\OrganigramActivity; // Para gestionar actividades
use App\Models\OrganigramSkill;    // Para gestionar habilidades
use App\Models\OrganigramPosition; // Importa el nuevo modelo OrganigramPosition
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Para gestionar fotos de perfil
use Illuminate\Validation\Rule;
use App\Models\OrganigramTrajectory; // Importa el modelo OrganigramTrajectory

class OrganigramController extends Controller
{
    /**
     * Display a listing of the organigram members.
     * Muestra una lista de todos los miembros del organigrama.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Cargar miembros con sus relaciones para mostrar la jerarquía y detalles
        // Incluye la relación 'position' para poder acceder al nombre del puesto.
        $members = OrganigramMember::with(['area', 'manager', 'subordinates', 'activities', 'skills', 'trajectories', 'position']) // AÑADIDO 'position'
                                    ->orderBy('area_id')
                                    ->get(); // Obtener todos los miembros ordenados por área inicialmente

        // Opción 2: Ordenar la colección por el nombre de la posición después de obtenerla.
        // Puedes cambiar a 'hierarchy_level' si esa columna está implementada y prefieres ese orden.
        $members = $members->sortBy(function($member) {
            // Usa el nombre del puesto para ordenar, si no tiene puesto, se considera vacío para el orden.
            return $member->position->name ?? '';
        });

        // Opcional: Construir una estructura jerárquica para la vista
        $hierarchicalMembers = $members->whereNull('manager_id')->map(function ($member) use ($members) {
            return $this->buildHierarchy($member, $members);
        });

        return view('admin.organigram.index', compact('members', 'hierarchicalMembers'));
    }

    /**
     * Helper para construir la jerarquía (recursivo)
     */
    protected function buildHierarchy($member, $allMembers)
    {
        $member->children = $allMembers->where('manager_id', $member->id)->map(function ($child) use ($allMembers) {
            return $this->buildHierarchy($child, $allMembers);
        })->values(); // values() para reindexar el array

        return $member;
    }

    /**
     * Show the form for creating a new organigram member.
     * Muestra el formulario para crear un nuevo miembro del organigrama.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $areas = Area::orderBy('name')->get();
        $managers = OrganigramMember::orderBy('name')->get(); // Posibles managers
        $activities = OrganigramActivity::orderBy('name')->get(); // Todas las actividades disponibles
        $skills = OrganigramSkill::orderBy('name')->get(); // Todas las habilidades disponibles
        $positions = OrganigramPosition::orderBy('hierarchy_level')->orderBy('name')->get(); // NUEVO: Obtener posiciones

        return view('admin.organigram.create', compact('areas', 'managers', 'activities', 'skills', 'positions')); // Añadir 'positions'
    }

    /**
     * Store a newly created organigram member in storage.
     * Almacena un nuevo miembro del organigrama en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'cell_phone' => 'nullable|string|max:20',
            // CAMBIADO: 'position' a 'position_id'
            'position_id' => 'required|exists:organigram_positions,id',
            'area_id' => 'required|exists:areas,id',
            'manager_id' => 'nullable|exists:organigram_members,id',
            'profile_photo' => 'nullable|image|max:2048', // Max 2MB
            // Validación para actividades, habilidades y trayectoria
            'activities_ids' => 'nullable|array',
            'activities_ids.*' => 'exists:organigram_activities,id',
            'skills_ids' => 'nullable|array',
            'skills_ids.*' => 'exists:organigram_skills,id',
            'trajectories' => 'nullable|array',
            'trajectories.*.title' => 'required_with:trajectories.*.start_date|string|max:255',
            'trajectories.*.description' => 'nullable|string|max:1000',
            'trajectories.*.start_date' => 'nullable|date',
            'trajectories.*.end_date' => 'nullable|date|after_or_equal:trajectories.*.start_date',
        ]);

        $path = null;
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('organigram-photos', 'public');
        }

        $member = OrganigramMember::create([
            'name' => $request->name,
            'email' => $request->email,
            'cell_phone' => $request->cell_phone,
            'position_id' => $request->position_id, // CAMBIADO
            'area_id' => $request->area_id,
            'manager_id' => $request->manager_id,
            'profile_photo_path' => $path,
        ]);

        // Sincronizar actividades y habilidades
        $member->activities()->sync($request->input('activities_ids', []));
        $member->skills()->sync($request->input('skills_ids', []));

        // Guardar trayectoria
        if ($request->has('trajectories')) {
            foreach ($request->trajectories as $trajectoryData) {
                // Eliminar el 'id' si está presente para no intentar crearlo con un ID que no existe en la BD
                unset($trajectoryData['id']);
                $member->trajectories()->create($trajectoryData);
            }
        }

        return redirect()->route('admin.organigram.index')->with('success', 'Miembro del organigrama creado exitosamente.');
    }

    /**
     * Show the form for editing the specified organigram member.
     * Muestra el formulario para editar un miembro del organigrama.
     *
     * @param  \App\Models\OrganigramMember  $organigramMember
     * @return \Illuminate\View\View
     */
    public function edit(OrganigramMember $organigramMember)
    {
        $areas = Area::orderBy('name')->get();
        // Excluir al propio miembro de la lista de posibles managers para evitar auto-referencias
        $managers = OrganigramMember::where('id', '!=', $organigramMember->id)->orderBy('name')->get();
        $activities = OrganigramActivity::orderBy('name')->get();
        $skills = OrganigramSkill::orderBy('name')->get();
        $positions = OrganigramPosition::orderBy('hierarchy_level')->orderBy('name')->get(); // NUEVO: Obtener posiciones

        // Cargar relaciones para la vista de edición. AÑADIDO 'position'
        $organigramMember->load(['activities', 'skills', 'trajectories', 'position']);

        // IDs de actividades y habilidades actuales para marcar checkboxes
        $memberActivitiesIds = $organigramMember->activities->pluck('id')->toArray();
        $memberSkillsIds = $organigramMember->skills->pluck('id')->toArray();

        return view('admin.organigram.edit', compact('organigramMember', 'areas', 'managers', 'activities', 'skills', 'memberActivitiesIds', 'memberSkillsIds', 'positions')); // Añadir 'positions'
    }

    /**
     * Update the specified organigram member in storage.
     * Actualiza el miembro del organigrama en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrganigramMember  $organigramMember
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, OrganigramMember $organigramMember)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'cell_phone' => 'nullable|string|max:20',
            // CAMBIADO: 'position' a 'position_id'
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
        ]);

        // Lógica para foto de perfil
        if ($request->hasFile('profile_photo')) {
            if ($organigramMember->profile_photo_path) {
                Storage::disk('public')->delete($organigramMember->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('organigram-photos', 'public');
            $organigramMember->profile_photo_path = $path;
        } elseif ($request->input('remove_profile_photo')) {
            if ($organigramMember->profile_photo_path) {
                Storage::disk('public')->delete($organigramMember->profile_photo_path);
                $organigramMember->profile_photo_path = null;
            }
        }

        $organigramMember->fill($request->except(['profile_photo', 'remove_profile_photo', 'activities_ids', 'skills_ids', 'trajectories']));
        $organigramMember->save();

        // Sincronizar actividades y habilidades
        $organigramMember->activities()->sync($request->input('activities_ids', []));
        $organigramMember->skills()->sync($request->input('skills_ids', []));

        // Actualizar/Crear/Eliminar trayectoria
        $existingTrajectoryIds = $organigramMember->trajectories->pluck('id')->toArray();
        $updatedTrajectoryIds = [];

        if ($request->has('trajectories')) {
            foreach ($request->trajectories as $trajectoryData) {
                if (isset($trajectoryData['id']) && $trajectoryData['id'] != null) { // Asegurarse que el ID no sea nulo para update
                    // Actualizar existente
                    $organigramMember->trajectories()->where('id', $trajectoryData['id'])->update($trajectoryData);
                    $updatedTrajectoryIds[] = $trajectoryData['id'];
                } else {
                    // Crear nuevo
                    unset($trajectoryData['id']); // Asegurarse de que no se intente usar un ID para una nueva creación
                    $newTrajectory = $organigramMember->trajectories()->create($trajectoryData);
                    $updatedTrajectoryIds[] = $newTrajectory->id;
                }
            }
        }
        // Eliminar trayectorias que no fueron enviadas en el request
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
     * Elimina el miembro del organigrama de la base de datos.
     *
     * @param  \App\Models\OrganigramMember  $organigramMember
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(OrganigramMember $organigramMember)
    {
        // Eliminar foto de perfil si existe
        if ($organigramMember->profile_photo_path) {
            Storage::disk('public')->delete($organigramMember->profile_photo_path);
        }

        $organigramMember->delete(); // Esto también eliminará trayectorias y relaciones pivote

        return redirect()->route('admin.organigram.index')->with('success', 'Miembro del organigrama eliminado exitosamente.');
    }

    /**
     * Show the form for managing Activities.
     * Muestra el formulario para gestionar Actividades.
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
     * Muestra el formulario para gestionar Habilidades.
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

    public function getInteractiveOrganigramData()
    {
        $members = OrganigramMember::with(['area', 'manager.position', 'position', 'activities', 'skills', 'trajectories'])->get();
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

        // 2. Nodos de Áreas (CON CAMBIOS)
        foreach ($areas as $area) {
            $flatNodes[] = [
                'id' => 'area_' . $area->id,
                'pid' => 'org_root',
                'name' => $area->name,
                'title' => 'Área',
                'type' => 'area',
                'img' => $area->icon_path ? asset('storage/' . $area->icon_path) : null,
                'description' => $area->description, // <-- CAMBIO: Añadido para el modal
            ];
        }

        // 3. Nodos de Miembros
        foreach ($members as $member) {
            $parentId = $member->manager_id ? (string)$member->manager_id : 'area_' . $member->area_id;
            
            $flatNodes[] = [
                'id' => (string)$member->id,
                'pid' => $parentId,
                'name' => $member->name,
                'title' => $member->position->name ?? 'Sin Posición',
                'img' => $member->profile_photo_path ? asset('storage/' . $member->profile_photo_path) : null,
                'type' => 'member',
                'full_details' => [ // Objeto con toda la información para el modal del miembro
                    'name' => $member->name,
                    'email' => $member->email,
                    'cell_phone' => $member->cell_phone,
                    'position_name' => $member->position->name ?? 'N/A',
                    'area_name' => $member->area->name ?? 'N/A',
                    'manager_name' => $member->manager->name ?? 'N/A',
                    'profile_photo_path' => $member->profile_photo_path ? asset('storage/' . $member->profile_photo_path) : null,
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
     * Muestra la vista para el organigrama interactivo.
     * @return \Illuminate\View\View
     */
    public function interactiveOrganigram()
    {
        return view('admin.organigram.interactive');
    }

    /**
     * Convierte una lista plana de nodos en un árbol anidado.
     */
    private function buildNestedTree(array &$elements, $parentId = null, int $depth = 0) {
        $branch = [];
        foreach ($elements as &$element) {
            if ($element['pid'] == $parentId) {
                if ($depth >= 1) {
                    $element['collapsed'] = true;
                }
                $children = $this->buildNestedTree($elements, $element['id'], $depth + 1);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
                unset($element);
            }
        }
        return $branch;
    }
}
