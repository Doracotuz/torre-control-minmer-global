<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

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

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(OrganigramMember::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(OrganigramMember::class, 'manager_id');
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(OrganigramActivity::class, 'organigram_member_activity');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(OrganigramSkill::class, 'organigram_member_skill');
    }

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

    protected $appends = ['profile_photo_path_url'];    

    protected function profilePhotoPathUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile_photo_path
                            ? Storage::disk('s3')->url($this->profile_photo_path)
                            : null,
        );
    }    
    
}