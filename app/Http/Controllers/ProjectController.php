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
use App\Models\ProjectHistory;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

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

        $visibleProjects = $projectsQuery->with(['expenses', 'tasks.assignee', 'leader'])
            ->get()
            ->map(function ($project) {
                $completedTasks = $project->tasks->where('status', 'Completada')->count();
                $totalTasks = $project->tasks->count();
                
                $project->progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                $project->spent = $project->expenses->sum('amount');
                $project->tasks_count = $totalTasks;
                $project->tasks_completed_count = $completedTasks;

                if ($project->status === 'Completado' || $project->status === 'Cancelado') {
                    $project->health_status = 'archived';
                } elseif (!$project->due_date) {
                    $project->health_status = 'on-track';
                } elseif ($project->due_date < now()) {
                    $project->health_status = 'overdue';
                } elseif (Carbon::parse($project->due_date)->between(now(), now()->addDays(7))) {
                    $project->health_status = 'at-risk';
                } else {
                    $project->health_status = 'on-track';
                }

                return $project;
            });

        $myPendingTasks = Task::where('assignee_id', $user->id)
            ->where('status', '!=', 'Completada')
            ->with('project')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $recentActivity = \App\Models\ProjectHistory::whereIn('project_id', $visibleProjects->pluck('id'))
                            ->with('user', 'project')
                            ->latest()
                            ->limit(5)
                            ->get();

        return view('projects.index', compact(
            'visibleProjects', 
            'myPendingTasks', 
            'recentActivity'
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

    public function generateReportPdf(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(
            'leader', 
            'areas',
            'tasks.assignee', 
            'expenses.user', 
            'files.user', 
            'history.user',
            'comments.user'
        );

        $tasksTotal = $project->tasks->count();
        $tasksCompleted = $project->tasks->where('status', 'Completada')->count();
        $tasksInProgress = $project->tasks->where('status', 'En Progreso')->count();
        $tasksPending = $project->tasks->where('status', 'Pendiente')->count();
        $progress = $tasksTotal > 0 ? ($tasksCompleted / $tasksTotal) * 100 : 0;

        $totalSpent = $project->expenses->sum('amount');
        $budget = $project->budget ?? 0;
        $budgetProgress = $budget > 0 ? ($totalSpent / $budget) * 100 : 0;
        
        $daysRemaining = null;
        $healthStatus = 'on-track'; 
        if ($project->due_date) {
            $daysRemaining = (int) now()->diffInDays($project->due_date, false); 
            if ($daysRemaining < 0 && $project->status !== 'Completado') {
                $healthStatus = 'overdue';
            } elseif ($daysRemaining <= 7 && $project->status !== 'Completado') {
                $healthStatus = 'at-risk';
            }
        }

        $diagnosis_level = 'success'; 
        $diagnosis_message = "Proyecto saludable y operando dentro de los parámetros esperados.";
        if ($healthStatus == 'overdue' || $budgetProgress > 100) {
            $diagnosis_level = 'danger';
            $diagnosis_message = "ALERTA CRÍTICA: El proyecto está vencido y/o ha excedido su presupuesto. Requiere intervención inmediata.";
        } elseif ($healthStatus == 'at-risk' || $budgetProgress > 90) {
            $diagnosis_level = 'warning';
            $diagnosis_message = "ADVERTENCIA: El proyecto presenta riesgo de vencimiento y/o está cerca de agotar su presupuesto. Se recomienda monitoreo cercano.";
        } elseif ($progress < 50 && $budgetProgress > 75) {
             $diagnosis_level = 'warning';
             $diagnosis_message = "ADVERTENCIA: El presupuesto se está consumiendo más rápido que el avance de las tareas. Revisar alcance y gastos.";
        }

        $teamWorkload = $project->tasks
            ->whereNotNull('assignee_id')
            ->groupBy('assignee_id')
            ->map(function ($tasks) {
                $totalActive = $tasks->where('status', '!=', 'Completada')->count();
                $overdue = $tasks->where('status', '!=', 'Completada')->where('due_date', '<', now())->count();
                $onTrack = $totalActive - $overdue;
                
                return [
                    'name' => $tasks->first()->assignee->name,
                    'total_active' => $totalActive,
                    'on_track' => $onTrack,
                    'overdue' => $overdue,
                ];
            })
            ->sortByDesc('total_active'); 
        
        $overdueTasks = $project->tasks
            ->where('status', '!=', 'Completada')
            ->where('due_date', '<', now())
            ->sortBy('due_date');

        $logoPath = 'LogoAzul.png';
        $logoBase64 = null;
        if (Storage::disk('s3')->exists($logoPath)) {
            $logoContent = Storage::disk('s3')->get($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }
        
        $data = [
            'project' => $project,
            'logoBase64' => $logoBase64,
            'kpis' => [
                'progress' => $progress,
                'tasksTotal' => $tasksTotal,
                'tasksCompleted' => $tasksCompleted,
                'tasksInProgress' => $tasksInProgress,
                'tasksPending' => $tasksPending,
                'budget' => $budget,
                'totalSpent' => $totalSpent,
                'budgetProgress' => $budgetProgress,
                'daysRemaining' => $daysRemaining,
                'healthStatus' => $healthStatus,
            ],
            'diagnosis' => [
                'level' => $diagnosis_level,
                'message' => $diagnosis_message,
            ],
            'teamWorkload' => $teamWorkload,
            'overdueTasks' => $overdueTasks,
            'comments' => $project->comments->sortByDesc('created_at'),
            'allTasks' => $project->tasks->sortBy('due_date'),
        ];

        $pdf = Pdf::loadView('projects.report', $data);
        return $pdf->stream('Dossier_Proyecto_' . $project->id . '.pdf');
    }

}