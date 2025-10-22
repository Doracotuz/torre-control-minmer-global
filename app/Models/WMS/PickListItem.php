<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickListItem extends Model {
    use HasFactory;
    protected $fillable = ['pick_list_id', 'product_id', 'location_id', 'quantity_to_pick', 'quantity_picked', 'is_picked', 'quality_id'];
    public function product() { return $this->belongsTo(\App\Models\Product::class); }
    public function location() { return $this->belongsTo(\App\Models\Location::class); }
}