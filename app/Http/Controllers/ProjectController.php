<?php

namespace App\Http\Controllers;

// Importaciones de todos los modelos y facades que utilizaremos
use App\Models\Area;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;
    /**
     * Muestra el dashboard principal de proyectos con KPIs y gráficos.
     * Los datos mostrados están filtrados según los permisos del usuario.
     */
    public function index()
    {
        $this->authorize('viewAny', Project::class);
        $user = Auth::user();

        // Creamos una consulta base que solo obtiene los proyectos que el usuario puede ver
        $projectsQuery = Project::query();
        if (!$user->isSuperAdmin()) {
            $projectsQuery->where(function ($q) use ($user) {
                $q->where('leader_id', $user->id)
                  ->orWhereHas('tasks', fn($t) => $t->where('assignee_id', $user->id))
                  ->orWhereHas('areas', fn($a) => $a->where('area_id', $user->area_id));
            });
        }
        
        // --- KPIs para las tarjetas (calculados sobre los proyectos permitidos) ---
        $activeProjectsCount = (clone $projectsQuery)->whereIn('status', ['Planeación', 'En Progreso', 'En Pausa'])->count();
        $overdueProjectsCount = (clone $projectsQuery)->where('due_date', '<', now())->whereNotIn('status', ['Completado', 'Cancelado'])->count();
        $upcomingDeadlinesCount = (clone $projectsQuery)->whereBetween('due_date', [now(), now()->addDays(7)])->count();
        $completedThisMonthCount = (clone $projectsQuery)->where('status', 'Completado')->whereMonth('updated_at', now()->month)->count();

        // --- Datos para los Gráficos y Listas ---
        $allowedProjectIds = (clone $projectsQuery)->pluck('id');

        $teamWorkload = Task::whereIn('project_id', $allowedProjectIds)
            ->where('status', '!=', 'Completada')->whereNotNull('assignee_id')->with('assignee')
            ->select('assignee_id', DB::raw('count(*) as tasks_count'))->groupBy('assignee_id')
            ->orderBy('tasks_count', 'desc')->get()
            ->map(fn ($item) => ['name' => $item->assignee->name ?? 'Sin asignar', 'tasks' => $item->tasks_count]);

        $upcomingProjects = (clone $projectsQuery)->whereBetween('due_date', [now(), now()->addDays(14)])
            ->orderBy('due_date', 'asc')->get();
            
        $activeProjects = (clone $projectsQuery)->with('tasks')->whereIn('status', ['En Progreso', 'Planeación'])->get();
        $totalProgress = 0; $progressCount = 0;
        foreach ($activeProjects as $project) {
            $totalTasks = $project->tasks->count();
            if ($totalTasks > 0) {
                $completedTasks = $project->tasks->where('status', 'Completada')->count();
                $totalProgress += ($completedTasks / $totalTasks) * 100;
                $progressCount++;
            }
        }
        $overallProgress = $progressCount > 0 ? $totalProgress / $progressCount : 0;

        $projectsByStatus = (clone $projectsQuery)->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status');

        $recentActivity = ProjectComment::whereIn('project_id', $allowedProjectIds)
            ->with('user', 'project')->latest()->limit(5)->get();

        $chartData = [
            'status' => ['labels' => $projectsByStatus->keys(), 'series' => $projectsByStatus->values()],
            'workload' => ['labels' => $teamWorkload->pluck('name'), 'series' => $teamWorkload->pluck('tasks')],
            'overallProgress' => round($overallProgress),
        ];

        return view('projects.index', compact(
            'activeProjectsCount', 'overdueProjectsCount', 'upcomingDeadlinesCount', 'completedThisMonthCount', 'chartData', 'upcomingProjects', 'recentActivity'
        ));
    }

    /**
     * Muestra el tablero Kanban con los proyectos filtrados por permisos.
     */
    public function list()
    {
        $this->authorize('viewAny', Project::class);
        $user = auth()->user();
        $statuses = ['Planeación', 'En Progreso', 'En Pausa', 'Completado'];

        $query = Project::query();
        if (!$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('leader_id', $user->id)
                  ->orWhereHas('tasks', fn($t) => $t->where('assignee_id', $user->id))
                  ->orWhereHas('areas', fn($a) => $a->where('area_id', $user->area_id));
            });
        }

        $projectsByStatus = $query->with('leader')->whereIn('status', $statuses)->get()->groupBy('status');

        foreach ($statuses as $status) {
            if (!$projectsByStatus->has($status)) {
                $projectsByStatus[$status] = collect();
            }
        }
        
        return view('projects.list', compact('projectsByStatus', 'statuses'));
    }

    /**
     * Muestra el formulario para crear un nuevo proyecto.
     */
    public function create()
    {
        $this->authorize('create', Project::class);
        $users = User::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        return view('projects.create', compact('users', 'areas'));
    }

    /**
     * Guarda un nuevo proyecto en la base de datos.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'leader_id' => 'nullable|exists:users,id',
            'budget' => 'nullable|numeric|min:0',
            'areas' => 'nullable|array',
        ]);

        $project = Project::create($validatedData);
        if ($request->has('areas')) {
            $project->areas()->sync($request->areas);
        }

        return redirect()->route('projects.index')->with('success', '¡Proyecto creado exitosamente!');
    }

    /**
     * Muestra la página de detalle de un proyecto específico.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project->load('leader', 'tasks.assignee', 'comments.user', 'files.user');

        $completedTasks = $project->tasks->where('status', 'Completada')->count();
        $totalTasks = $project->tasks->count();
        $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        
        $users = User::orderBy('name')->get();

        $timelineData = $project->tasks->map(function ($task) {
            $startDate = Carbon::parse($task->created_at);
            $endDate = $task->due_date ? Carbon::parse($task->due_date) : $startDate->copy()->addDay();
            $statusColors = [ 'Completada' => '#28a745', 'En Progreso' => '#0d6efd', 'Pendiente' => '#ffc107' ];
            $priorityPrefix = match($task->priority) { 'Alta' => '🔥 ', 'Media' => '🔸 ', 'Baja' => '🔹 ', default => '' };
            return [
                'x' => $priorityPrefix . $task->name,
                'y' => [$startDate->valueOf(), $endDate->valueOf()],
                'fillColor' => $statusColors[$task->status] ?? '#6c757d'
            ];
        });

        $timelineMinDate = $project->tasks->isNotEmpty() ? Carbon::parse($project->tasks->min('created_at')) : null;
        $timelineInitialMaxDate = $timelineMinDate ? $timelineMinDate->copy()->addDays(45) : null;

        return view('projects.show', compact('project', 'progress', 'users', 'timelineData', 'timelineMinDate', 'timelineInitialMaxDate'));
    }

    /**
     * Muestra el formulario para editar un proyecto.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        $users = User::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $project->load('areas');
        return view('projects.edit', compact('project', 'users', 'areas'));
    }

    /**
     * Actualiza un proyecto en la base de datos.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'leader_id' => 'nullable|exists:users,id',
            'status' => 'required|in:Planeación,En Progreso,En Pausa,Completado,Cancelado',
            'budget' => 'nullable|numeric|min:0',
            'areas' => 'nullable|array',
        ]);

        $project->update($validatedData);
        $project->areas()->sync($request->input('areas', []));

        return redirect()->route('projects.list')->with('success', '¡Proyecto actualizado exitosamente!');
    }

    /**
     * Elimina un proyecto de la base de datos.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect()->route('projects.list')->with('success', '¡Proyecto eliminado exitosamente!');
    }

    /**
     * Actualiza el estatus de un proyecto (usado por el Kanban).
     */
    public function updateStatus(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $request->validate(['status' => 'required|in:Planeación,En Progreso,En Pausa,Completado,Cancelado']);
        $project->update(['status' => $request->status]);
        return response()->json(['message' => 'Estatus del proyecto actualizado.']);
    }
}