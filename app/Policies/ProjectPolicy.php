<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return Project::where('leader_id', $user->id)
            ->orWhereHas('tasks', fn($q) => $q->where('assignee_id', $user->id))
            ->orWhereHas('areas', fn($q) => $q->where('area_id', $user->area_id))
            ->exists();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->id === $project->leader_id) return true;
        if ($project->tasks()->where('assignee_id', $user->id)->exists()) return true;
        if ($project->areas()->where('area_id', $user->area_id)->exists()) return true;

        return false;
    }

    public function create(User $user): bool
    {
        return !$user->is_client;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }
}