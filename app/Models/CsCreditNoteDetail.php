<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsCreditNoteDetail extends Model
{
    use HasFactory;

    protected $table = 'cs_credit_note_details';

    protected $guarded = [];

    public function creditNote()
    {
        return $this->belongsTo(CsCreditNote::class, 'cs_credit_note_id');
    }

    public function product()
    {
        return $this->belongsTo(CsProduct::class, 'sku', 'sku');
    }
}