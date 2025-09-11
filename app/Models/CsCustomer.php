<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsCustomer extends Model
{
    use HasFactory;

    protected $table = 'cs_customers';

    protected $fillable = [
        'client_id',
        'name',
        'channel',
        'delivery_specifications', // Añadir la nueva columna aquí
        'created_by_user_id',
        'updated_by_user_id',
    ];

    // Añadir el casting para que Laravel lo trate como un array
    protected $casts = [
        'delivery_specifications' => 'array',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}