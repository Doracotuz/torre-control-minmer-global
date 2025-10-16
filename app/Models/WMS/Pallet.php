<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Pallet extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'lpn',
        'location_id',
        'status',
        'purchase_order_id',
    ];

    /**
     * Una tarima pertenece a una Orden de Compra.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Una tarima pertenece a una Ubicación.
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    /**
     * Una tarima contiene muchos items (productos).
     */
    public function items()
    {
        return $this->hasMany(PalletItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }   
     
}