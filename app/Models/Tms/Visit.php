<?php

namespace App\Models\Tms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tms_visits';

    /**
     * The attributes that are mass assignable.
     *
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
        'exit_datetime',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visit_datetime' => 'datetime',
        'exit_datetime' => 'datetime',
        'companions' => 'array',
    ];

    /**
     * Get the user who created the visit.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }
    
}
