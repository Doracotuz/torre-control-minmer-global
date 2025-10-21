<?php
namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;

class InventoryAdjustment extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * Este array debe coincidir con las columnas de tu tabla 'inventory_adjustments'.
     */
    protected $fillable = [
        'physical_count_task_id', 
        'pallet_item_id',
        'product_id', 
        'location_id', 
        'quantity_before',
        'quantity_after',
        'quantity_difference', 
        'reason', 
        'user_id', 
        'source',
    ];

    /**
     * Define la relación con el Usuario que realizó el ajuste.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación con el item de la tarima que fue ajustado.
     * Esta relación es opcional (nullable) porque un ajuste puede no venir de un LPN.
     */
    public function palletItem()
    {
        return $this->belongsTo(PalletItem::class);
    }

    /**
     * Define la relación con el Producto afectado.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Define la relación con la Ubicación afectada.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}