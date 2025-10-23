<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrganigramMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'cell_phone',
        'position_id',
        'profile_photo_path',
        'area_id',
        'manager_id',
        'is_active',
    ];

    /**
     * Get the area that the organigram member belongs to.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the manager (jefe) of the organigram member.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(OrganigramMember::class, 'manager_id');
    }

    /**
     * Get the subordinates (subordinados) of the organigram member.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(OrganigramMember::class, 'manager_id');
    }

    /**
     * The activities that belong to the organigram member.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(OrganigramActivity::class, 'organigram_member_activity');
    }

    /**
     * The skills that belong to the organigram member.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(OrganigramSkill::class, 'organigram_member_skill');
    }

    /**
     * Get the trajectory entries for the organigram member.
     */
    public function trajectories(): HasMany
    {
        return $this->hasMany(OrganigramTrajectory::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(OrganigramPosition::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'organigram_member_id');
    }    

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    public function userResponsivas()
    {
        return $this->hasMany(UserResponsiva::class)->latest();
    }

    
}