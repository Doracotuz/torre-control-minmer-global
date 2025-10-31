<?php

namespace App\Http\Controllers;

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
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        $user = Auth::user();

        $projectsQuery = Project::query();
        if (!$user->isSuperAdmin()) {
            $projectsQuery->where(function ($q) use ($user) {
                $q->where('leader_id', $user->id)
                ->orWhereHas('tasks', fn($t) => $t->where('assignee_id', $user->id))
                ->orWhereHas('areas', fn($a) => $a->where('area_id', $user->area_id));
            });
        }

        $projectsQuery->distinct();        

        $visibleProjects = (clone $projectsQuery)->with('expenses')->get();

        $totalBudget = $visibleProjects->sum('budget');
        $totalSpent = $visibleProjects->sum(function ($project) {
            return $project->expenses->sum('amount');
        });

        $financialData = $visibleProjects->where('budget', '>', 0)->map(function ($project) {
            return [
                'name' => $project->name,
                'budget' => (float) $project->budget,
                'spent' => (float) $project->expenses->sum('amount'),
            ];
        })->sortByDesc(function ($item) {
            return $item['budget'] > 0 ? ($item['spent'] / $item['budget']) : 0;
        })->values();
        $activeProjectsCount = $visibleProjects->whereIn('status', ['Planeación', 'En Progreso', 'En Pausa'])->count();
        $overdueProjectsCount = $visibleProjects->where('due_date', '<', now())->whereNotIn('status', ['Completado', 'Cancelado'])->count();

        $upcomingProjects = $visibleProjects->whereBetween('due_date', [now(), now()->addDays(14)])->sortBy('due_date');
        $overdueProjects = $visibleProjects->where('due_date', '<', now())->whereNotIn('status', ['Completado', 'Cancelado']);
        $teamWorkload = Task::whereIn('project_id', $visibleProjects->pluck('id'))
            ->where('status', '!=', 'Completada')->whereNotNull('assignee_id')->with('assignee')
            ->select('assignee_id', DB::raw('count(*) as tasks_count'))->groupBy('assignee_id')
            ->orderBy('tasks_count', 'desc')->get()
            ->map(fn ($item) => ['name' => $item->assignee->name ?? 'Sin asignar', 'tasks' => $item->tasks_count]);
        $projectsByStatus = $visibleProjects->groupBy('status')->map->count();
        $recentActivity = \App\Models\ProjectHistory::whereIn('project_id', $visibleProjects->pluck('id'))
                                ->with('user', 'project')
                                ->latest()
                                ->limit(7)
                                ->get();

        $chartData = [
            'status' => ['labels' => $projectsByStatus->keys(), 'series' => $projectsByStatus->values()],
            'workload' => ['labels' => $teamWorkload->pluck('name'), 'series' => $teamWorkload->pluck('tasks')],
            'financials' => $financialData,
        ];

        return view('projects.index', compact(
            'activeProjectsCount', 'overdueProjectsCount', 'totalBudget', 'totalSpent', 'chartData', 'upcomingProjects', 'overdueProjects', 'recentActivity'
        ));
    }

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

        $query->distinct();        

        $projectsByStatus = $query->with('leader', 'tasks') 
                                  ->whereIn('status', $statuses)
                                  ->get()
                                  ->groupBy('status');

        foreach ($statuses as $status) {
            if (!$projectsByStatus->has($status)) {
                $projectsByStatus[$status] = collect();
            }
        }
        
        return view('projects.list', compact('projectsByStatus', 'statuses'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);
        $users = User::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        return view('projects.create', compact('users', 'areas'));
    }

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

    public function show(Project $project)
    {
        $project->load('leader', 'tasks.assignee', 'comments.user', 'files.user', 'expenses');

        $completedTasks = $project->tasks->where('status', 'Completada')->count();
        $totalTasks = $project->tasks->count();
        $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        $users = User::orderBy('name')->get();

        $timelineData = $project->tasks->map(function ($task) {
            $startDate = Carbon::parse($task->created_at)->startOfDay();

            $endDate = $task->due_date ? Carbon::parse($task->due_date)->endOfDay() : $startDate->copy()->endOfDay();

            $statusColors = [ 'Completada' => '#28a745', 'En Progreso' => '#0d6efd', 'Pendiente' => '#ffc107' ];
            $priorityPrefix = match($task->priority) { 'Alta' => '🔥 ', 'Media' => '🔸 ', 'Baja' => '🔹 ', default => '' };

            return [
                'x' => $priorityPrefix . $task->name,
                'y' => [$startDate->valueOf(), $endDate->valueOf()],
                'fillColor' => $statusColors[$task->status] ?? '#6c757d'
            ];
        });

        $timelineMinDate = null;
        if ($project->tasks->isNotEmpty()) {
            $timelineMinDate = Carbon::parse($project->tasks->min('created_at'))->startOfDay();
        }
        $timelineInitialMaxDate = $timelineMinDate ? $timelineMinDate->copy()->addDays(45) : null;

        return view('projects.show', compact(
            'project', 'progress', 'users', 'timelineData', 'timelineMinDate', 'timelineInitialMaxDate'
        ));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        $users = User::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $project->load('areas');
        return view('projects.edit', compact('project', 'users', 'areas'));
    }

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

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect()->route('projects.list')->with('success', '¡Proyecto eliminado exitosamente!');
    }

    public function updateStatus(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $validated = $request->validate(['status' => 'required|in:Planeación,En Progreso,En Pausa,Completado,Cancelado']);
        
        $oldStatus = $project->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            $project->update(['status' => $newStatus]);

            $project->history()->create([
                'user_id' => Auth::id(),
                'action_type' => 'status_change',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Estatus del proyecto actualizado.']);
        }
        
        return back()->with('success_status', 'Estatus del proyecto actualizado exitosamente.');
    }

    public function review()
    {
        $this->authorize('viewAny', Project::class);
        $user = Auth::user();

        $query = Project::query();
        if (!$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('leader_id', $user->id)
                  ->orWhereHas('tasks', fn($t) => $t->where('assignee_id', $user->id))
                  ->orWhereHas('areas', fn($a) => $a->where('area_id', $user->area_id));
            });
        }

        $query->distinct();        

        $activeStatuses = ['Planeación', 'En Progreso', 'En Pausa'];
        
        $projects = $query->whereIn('status', $activeStatuses)
                          ->with(
                              'leader', 
                              'comments.user',
                              'areas', 
                              'tasks', 
                              'tasks.assignee'
                            ) 
                          ->orderBy('due_date', 'asc')
                          ->get();
        
        $leaders = $projects->pluck('leader')->whereNotNull()->unique('id')->sortBy('name');
        
        $areas = $projects->pluck('areas')->flatten()->whereNotNull()->unique('id')->sortBy('name');
        

        $statuses = ['Planeación', 'En Progreso', 'En Pausa', 'Completado', 'Cancelado'];

        return view('projects.review', compact('projects', 'statuses', 'leaders', 'areas'));
    }
}