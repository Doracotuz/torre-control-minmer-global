<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Guarda una nueva tarea para un proyecto específico.
     */
    public function store(Request $request, Project $project)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:Baja,Media,Alta',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // Añadimos el project_id a los datos validados
        $validatedData['project_id'] = $project->id;

        // Creamos la tarea
        Task::create($validatedData);

        // Redireccionamos de vuelta a la página del proyecto con un mensaje
        return back()->with('success_task', '¡Tarea añadida exitosamente!');
    }

    public function update(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:Baja,Media,Alta',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $task->update($validatedData);

        return back()->with('success_task', '¡Tarea actualizada exitosamente!');
    }

    /**
     * Actualiza solo el estatus de una tarea.
     */
    public function updateStatus(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:Pendiente,En Progreso,Completada',
        ]);

        $task->update($validatedData);

        return back()->with('success_task', '¡Estatus de la tarea actualizado!');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return back()->with('success_task', '¡Tarea eliminada exitosamente!');
    }    

}