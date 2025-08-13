<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsOrder extends Model
{
    use HasFactory;

    protected $table = 'cs_orders';
    protected $guarded = []; // Usar guarded en lugar de fillable para simplicidad

    // --- INICIA CORRECCIÓN: Se añade el casting de fechas ---
    protected $casts = [
        'creation_date' => 'date',
        'authorization_date' => 'date',
        'invoice_date' => 'date',
        'delivery_date' => 'date',
        'evidence_reception_date' => 'date',
        'evidence_cutoff_date' => 'date',
    ];
    // --- TERMINA CORRECCIÓN ---

    public function details() { return $this->hasMany(CsOrderDetail::class); }
    public function plan() { return $this->hasOne(CsPlan::class); }
    public function events() { return $this->hasMany(CsOrderEvent::class)->orderBy('created_at', 'desc'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
