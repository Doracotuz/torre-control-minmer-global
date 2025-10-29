<?php

namespace App\Models\Tms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'tms_visits';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'visitor_name',
        'visitor_last_name',
        'company',
        'email',
        'vehicle_make',
        'vehicle_model',
        'license_plate',
        'visit_datetime',
        'reason',
        'companions',
        'qr_code_token',
        'status',
        'created_by_user_id',
        'entry_datetime',
        'exit_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'visit_datetime' => 'datetime',
        'entry_datetime' => 'datetime',
        'exit_datetime' => 'datetime',
        'companions' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }
    
}
