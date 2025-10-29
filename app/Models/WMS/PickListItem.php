<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Location;
use App\Models\WMS\Pallet;
use App\Models\WMS\Quality;

class PickListItem extends Model {
    use HasFactory;
    
    protected $fillable = [
        'pick_list_id', 
        'product_id', 
        'pallet_id',
        'location_id', 
        'quality_id', 
        'quantity_to_pick', 
        'quantity_picked', 
        'is_picked',
        'picked_at'
    ];

    protected $casts = [
        'is_picked' => 'boolean',
        'picked_at' => 'datetime',
    ];    

    public function product() { return $this->belongsTo(Product::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function quality() { return $this->belongsTo(Quality::class); }
    public function pallet() { return $this->belongsTo(Pallet::class); }
}