<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;


class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'due_date',
        'budget',
        'leader_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];    

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class)->latest();
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->latest();
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class);
    }

    public function expenses()
    {
        return $this->hasMany(ProjectExpense::class)->latest('expense_date');
    }

    public function history(): HasMany
    {
        return $this->hasMany(ProjectHistory::class)->latest();
    }

    protected static function booted(): void
    {
        static::deleting(function (Project $project) {
            

            $project->tasks()->delete();
            $project->comments()->delete();
            $project->expenses()->delete();
            $project->history()->delete();
            $project->files()->delete();
            $project->areas()->detach();
    
            Storage::disk('s3')->deleteDirectory('project_files/' . $project->id);
        });
    }
}