<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsOrderDetail extends Model {
    use HasFactory;
    protected $table = 'cs_order_details';
    protected $guarded = [];
    public function order() { return $this->belongsTo(CsOrder::class); }
    // Nueva relación para obtener la descripción del producto
    public function product()
    {
        return $this->belongsTo(CsProduct::class, 'sku', 'sku');
    }

    public function upc()
    {
        return $this->hasOne(\App\Models\CsOrderDetailUpc::class);
    }    
}