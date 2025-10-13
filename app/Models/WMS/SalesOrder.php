<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model {
    use HasFactory;
    protected $fillable = ['so_number', 'invoice_number', 'customer_name', 'user_id', 'order_date', 'status', 'notes'];
    public function user() { return $this->belongsTo(\App\Models\User::class); }
    public function lines() { return $this->hasMany(SalesOrderLine::class); }
}