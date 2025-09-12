<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $guarded = [];
    protected $casts = [
        'warehouse_audit_data' => 'array',
        'patio_audit_data' => 'array',
        'loading_audit_data' => 'array',
    ];

    public function order() { return $this->belongsTo(CsOrder::class, 'cs_order_id'); }
    public function guia() { return $this->belongsTo(Guia::class); }
    public function auditor() { return $this->belongsTo(User::class, 'user_id'); }
}
