<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Realiza comprobaciones previas a la autorización.
     * Si el usuario es Super Admin, se le concede acceso a todo automáticamente.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Asumimos que tienes un método isSuperAdmin() en tu modelo User.
        // Si no, puedes usar: if ($user->area?->name === 'Administración')
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null; // Devuelve null para continuar con la verificación específica del método.
    }

    /**
     * Determina si el usuario puede ver la lista de proyectos (el dashboard/kanban).
     * El usuario debe estar involucrado en al menos UN proyecto.
     */
    public function viewAny(User $user): bool
    {
        // El usuario puede ver la sección de proyectos si:
        // 1. Es líder de algún proyecto.
        // 2. Tiene una tarea asignada en algún proyecto.
        // 3. Su área está involucrada en algún proyecto.
        return Project::where('leader_id', $user->id)
            ->orWhereHas('tasks', fn($q) => $q->where('assignee_id', $user->id))
            ->orWhereHas('areas', fn($q) => $q->where('area_id', $user->area_id))
            ->exists();
    }

    /**
     * Determina si el usuario puede ver UN proyecto específico.
     */
    public function view(User $user, Project $project): bool
    {
        // El usuario puede ver el proyecto si:
        // 1. Es el líder del proyecto.
        if ($user->id === $project->leader_id) return true;
        // 2. Tiene una tarea asignada en ESE proyecto.
        if ($project->tasks()->where('assignee_id', $user->id)->exists()) return true;
        // 3. Su área está involucrada en ESE proyecto.
        if ($project->areas()->where('area_id', $user->area_id)->exists()) return true;

        return false;
    }

    /**
     * Determina si el usuario puede crear un nuevo proyecto.
     * Por ahora, permitimos que cualquier empleado interno (no cliente) pueda crear proyectos.
     */
    public function create(User $user): bool
    {
        return !$user->is_client;
    }

    /**
     * Determina si el usuario puede actualizar un proyecto.
     * Solo permitimos al líder del proyecto.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }

    /**
     * Determina si el usuario puede eliminar un proyecto.
     * Solo permitimos al líder del proyecto.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }

    /**
     * Determina si el usuario puede restaurar un proyecto eliminado (si usas Soft Deletes).
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }

    /**
     * Determina si el usuario puede eliminar permanentemente un proyecto (si usas Soft Deletes).
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }
}