<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderLine extends Model {
    use HasFactory;
    protected $fillable = ['sales_order_id', 'product_id', 'quantity_ordered'];
    public function product() { return $this->belongsTo(\App\Models\Product::class); }
}