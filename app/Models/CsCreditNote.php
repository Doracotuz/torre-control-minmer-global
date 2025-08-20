<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsCreditNote extends Model
{
    use HasFactory;

    protected $table = 'cs_credit_notes';

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(CsOrder::class, 'cs_order_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function details()
    {
        return $this->hasMany(CsCreditNoteDetail::class);
    }
}