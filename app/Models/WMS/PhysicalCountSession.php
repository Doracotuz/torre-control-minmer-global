<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Importa el modelo User

class PhysicalCountSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'user_id',
        'status',
        'assigned_user_id',
        'warehouse_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(PhysicalCountTask::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_user_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class);
    }    

}