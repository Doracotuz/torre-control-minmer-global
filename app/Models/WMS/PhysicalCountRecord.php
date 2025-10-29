<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PhysicalCountRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_count_task_id',
        'user_id',
        'count_number',
        'counted_quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function physicalCountTask()
    {
        return $this->belongsTo(PhysicalCountTask::class);
    }
}