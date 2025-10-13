<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model {
    use HasFactory;
    protected $fillable = ['po_number', 'supplier_id', 'user_id', 'expected_date', 'status', 'notes',
        'container_number', 'document_invoice', 'pedimento_a4', 'pedimento_g1'];
    public function user() { return $this->belongsTo(\App\Models\User::class); }
    public function lines() { return $this->hasMany(PurchaseOrderLine::class); }
}