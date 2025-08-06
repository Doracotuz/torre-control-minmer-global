<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agent_id',
        'ticket_sub_category_id', // Actualizado
        'title',
        'description',
        'status',
        'priority',
        'attachment_path',
        'work_summary',
        'closure_evidence_path',
        'rating',
        'rating_comment',
    ];

    /**
     * The user who created the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The agent assigned to the ticket.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * The sub-category of the ticket.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(TicketSubCategory::class, 'ticket_sub_category_id');
    }

    /**
     * The replies to the ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'asc');
    }

    /**
     * The status history of the ticket.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class)->orderBy('created_at', 'asc');
    }

}