<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'agent_id', 'title', 'description', 'status', 'priority', 'attachment_path', 'closure_evidence_path', 'work_summary', 'category_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'asc');
    }

    public function statusHistories()
    {
        return $this->hasMany(TicketStatusHistory::class)->orderBy('created_at', 'asc');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class);
    }

}