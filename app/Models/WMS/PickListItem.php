<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product; // Asegúrate de tener estas clases
use App\Models\Location;
use App\Models\WMS\Pallet;
use App\Models\WMS\Quality;

class PickListItem extends Model {
    use HasFactory;
    
    // Añade 'pallet_id'
    protected $fillable = [
        'pick_list_id', 
        'product_id', 
        'pallet_id', // <-- Añadido
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
    public function quality() { return $this->belongsTo(Quality::class); } // Añade relación quality
    public function pallet() { return $this->belongsTo(Pallet::class); } // <-- Añade relación pallet
}