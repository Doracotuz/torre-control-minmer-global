<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ProjectComment;


class ProjectController extends Controller
{
    /**
     * Muestra una lista de los proyectos.
     */
    public function index()
    {
        // --- KPIs para las tarjetas ---
        $activeProjectsCount = Project::whereIn('status', ['Planeación', 'En Progreso', 'En Pausa'])->count();
        $overdueProjectsCount = Project::where('due_date', '<', now())->whereNotIn('status', ['Completado', 'Cancelado'])->count();
        $upcomingDeadlinesCount = Project::whereBetween('due_date', [now(), now()->addDays(7)])->count();
        $completedThisMonthCount = Project::where('status', 'Completado')->whereMonth('updated_at', now()->month)->count();

        // --- Datos para los Gráficos y Listas ---
        // 1. Carga de Trabajo del Equipo (Tareas activas por usuario)
        $teamWorkload = Task::where('status', '!=', 'Completada')
            ->whereNotNull('assignee_id')
            ->with('assignee')
            ->select('assignee_id', DB::raw('count(*) as tasks_count'))
            ->groupBy('assignee_id')
            ->orderBy('tasks_count', 'desc')
            ->get()
            ->map(function ($item) {
                return ['name' => $item->assignee->name ?? 'Sin asignar', 'tasks' => $item->tasks_count];
            });

        // 2. Proyectos con Próximos Vencimientos (próximos 14 días)
        $upcomingProjects = Project::whereBetween('due_date', [now(), now()->addDays(14)])
            ->orderBy('due_date', 'asc')
            ->get();

        // 3. Salud General de Proyectos (Progreso promedio de proyectos activos)
        $activeProjects = Project::with('tasks')->whereIn('status', ['En Progreso', 'Planeación'])->get();
        $totalProgress = 0;
        $progressCount = 0;
        foreach ($activeProjects as $project) {
            $totalTasks = $project->tasks->count();
            if ($totalTasks > 0) {
                $completedTasks = $project->tasks->where('status', 'Completada')->count();
                $totalProgress += ($completedTasks / $totalTasks) * 100;
                $progressCount++;
            }
        }
        $overallProgress = $progressCount > 0 ? $totalProgress / $progressCount : 0;

        // 4. Distribución por Estatus (ya lo teníamos, lo mantenemos)
        $projectsByStatus = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // 5. Actividad Reciente Global
        $recentActivity = ProjectComment::with('user', 'project')
            ->latest()
            ->limit(5)
            ->get();

        // Preparamos los datos para ApexCharts
        $chartData = [
            'status' => [
                'labels' => $projectsByStatus->keys(),
                'series' => $projectsByStatus->values(),
            ],
            'workload' => [
                'labels' => $teamWorkload->pluck('name'),
                'series' => $teamWorkload->pluck('tasks'),
            ],
            'overallProgress' => round($overallProgress),
        ];

        return view('projects.index', compact(
            'activeProjectsCount',
            'overdueProjectsCount',
            'upcomingDeadlinesCount',
            'completedThisMonthCount',
            'chartData',
            'upcomingProjects',
            'recentActivity'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo proyecto.
     */
    public function create()
    {
        // Obtenemos todos los usuarios para poder asignarlos como líderes
        $users = User::orderBy('name')->get();
        return view('projects.create', compact('users'));
    }

    /**
     * Guarda un nuevo proyecto en la base de datos.
     */
    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'leader_id' => 'nullable|exists:users,id',
        ]);

        // Creación del proyecto con los datos validados
        Project::create($validatedData);

        // Redirección a la lista de proyectos con un mensaje de éxito
        return redirect()->route('projects.index')
                         ->with('success', '¡Proyecto creado exitosamente!');
    }

    /**
     * Muestra un proyecto específico. (Lo implementaremos en la Fase 3)
     */
    public function show(Project $project)
    {
        $project->load('leader', 'tasks.assignee', 'comments.user', 'files.user');

        $completedTasks = $project->tasks->where('status', 'Completada')->count();
        $totalTasks = $project->tasks->count();
        $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        $users = User::orderBy('name')->get();

        $timelineData = $project->tasks->map(function ($task) {
            $startDate = Carbon::parse($task->created_at);
            $endDate = $task->due_date ? Carbon::parse($task->due_date) : $startDate->copy()->addDay();

            $statusColors = [
                'Completada' => '#28a745', 'En Progreso' => '#0d6efd', 'Pendiente' => '#ffc107',
            ];
            
            // --- INICIO: LÓGICA DE PREFIJOS PARA PRIORIDAD ---
            $priorityPrefix = match($task->priority) {
                'Alta' => '🔥 ',
                'Media' => '🔸 ',
                'Baja' => '🔹 ',
                default => ''
            };
            // --- FIN: LÓGICA DE PREFIJOS PARA PRIORIDAD ---

            return [
                'x' => $priorityPrefix . $task->name, // Añadimos el prefijo al nombre
                'y' => [$startDate->valueOf(), $endDate->valueOf()],
                'fillColor' => $statusColors[$task->status] ?? '#6c757d'
            ];
        });

        // --- INICIO: NUEVA LÓGICA PARA EL ZOOM ---
        // Buscamos la fecha de inicio más temprana entre todas las tareas.
        $timelineMinDate = null;
        if ($project->tasks->isNotEmpty()) {
            $timelineMinDate = Carbon::parse($project->tasks->min('created_at'));
        }

        // Definimos el rango de zoom inicial (ej. los primeros 45 días)
        $timelineInitialMaxDate = $timelineMinDate ? $timelineMinDate->copy()->addDays(3) : null;
        // --- FIN: NUEVA LÓGICA PARA EL ZOOM ---

        return view('projects.show', compact(
            'project', 
            'progress', 
            'users', 
            'timelineData',
            'timelineMinDate', // Pasamos la fecha mínima a la vista
            'timelineInitialMaxDate' // Pasamos la fecha máxima inicial a la vista
        ));
    }

    /**
     * Muestra el formulario para editar un proyecto. (Lo implementaremos después)
     */
    public function edit(Project $project)
    {
        // Pasamos el proyecto y la lista de usuarios a la vista de edición
        $users = User::orderBy('name')->get();
        return view('projects.edit', compact('project', 'users'));
    }

    /**
     * Actualiza un proyecto en la base de datos. (Lo implementaremos después)
     */
    public function update(Request $request, Project $project)
    {
        // Validación de los datos del formulario
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'leader_id' => 'nullable|exists:users,id',
            'status' => 'required|in:Planeación,En Progreso,En Pausa,Completado,Cancelado',
            'budget' => 'nullable|numeric|min:0',
        ]);

        // Actualizamos el proyecto con los datos validados
        $project->update($validatedData);

        // Redirección a la lista de proyectos con un mensaje de éxito
        return redirect()->route('projects.index')
                         ->with('success', '¡Proyecto actualizado exitosamente!');
    }

    /**
     * Elimina un proyecto de la base de datos. (Lo implementaremos después)
     */
    public function destroy(Project $project)
    {
        // Usamos la política de cascada que definimos en la migración
        // para que al eliminar el proyecto, también se eliminen sus tareas.
        $project->delete();

        return redirect()->route('projects.index')
                        ->with('success', '¡Proyecto eliminado exitosamente!');
    }

    public function list()
    {
        // Definimos el orden de las columnas
        $statuses = ['Planeación', 'En Progreso', 'En Pausa', 'Completado'];

        // Obtenemos los proyectos y los agrupamos por su estatus
        $projectsByStatus = Project::with('leader')
            ->whereIn('status', $statuses)
            ->get()
            ->groupBy('status');

        // Nos aseguramos de que todos los estatus existan en el array para renderizar las columnas vacías
        foreach ($statuses as $status) {
            if (!$projectsByStatus->has($status)) {
                $projectsByStatus[$status] = collect();
            }
        }

        return view('projects.list', [
            'projectsByStatus' => $projectsByStatus,
            'statuses' => $statuses
        ]);
    }

    public function updateStatus(Request $request, Project $project)
    {
        $request->validate(['status' => 'required|in:Planeación,En Progreso,En Pausa,Completado,Cancelado']);

        $project->update(['status' => $request->status]);

        return response()->json(['message' => 'Estatus del proyecto actualizado.']);
    }    

}