<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrganigramActivity extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * The organigram members that have this activity.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(OrganigramMember::class, 'organigram_member_activity');
    }
}
