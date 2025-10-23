<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Asegúrate de tener User importado

class PickList extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Asegúrate de incluir todos los campos que usas en create() o update().
     */
    protected $fillable = [
        'sales_order_id', 
        'user_id',        // Quién generó la lista
        'picker_id',      // Quién la completó
        'status', 
        'picked_at'       // <-- AÑADE ESTE CAMPO
        // 'staging_location_id', // Si añadiste esta columna
    ];

    /**
     * The attributes that should be cast.
     * Esto le dice a Laravel cómo tratar ciertos campos.
     */
    protected $casts = [
        'picked_at' => 'datetime', // <-- AÑADE ESTE CAST
    ];

    // --- RELACIONES ---

    public function salesOrder() 
    { 
        return $this->belongsTo(SalesOrder::class); 
    }
    
    public function items() 
    { 
        return $this->hasMany(PickListItem::class); 
    }

    // Relación con el usuario que GENERÓ la picklist (opcional, si user_id lo usas para eso)
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el usuario que COMPLETÓ el picking
    public function picker()
    {
        return $this->belongsTo(User::class, 'picker_id');
    }
}