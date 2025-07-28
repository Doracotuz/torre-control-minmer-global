<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiGeneral extends Model
{
    use HasFactory;

    // ▼▼ AÑADE ESTA LÍNEA ▼▼
    protected $guarded = [];
}