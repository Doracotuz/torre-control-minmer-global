<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Area;

class PhysicalCountSession extends Model
{
    use HasFactory;

    protected $table = 'physical_count_sessions';

    protected $fillable = [
        'name',
        'type',
        'status',
        'warehouse_id',
        'area_id',
        'user_id',
        'assigned_user_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function tasks()
    {
        return $this->hasMany(PhysicalCountTask::class, 'physical_count_session_id');
    }
}