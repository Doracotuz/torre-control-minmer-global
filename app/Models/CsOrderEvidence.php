<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsOrderEvidence extends Model
{
    use HasFactory;

    protected $table = 'cs_order_evidences';

    protected $fillable = ['cs_order_id', 'file_name', 'file_path'];

    public function order()
    {
        return $this->belongsTo(CsOrder::class, 'cs_order_id');
    }
}