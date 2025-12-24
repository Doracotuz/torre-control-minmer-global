<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectronicLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'series',
        'consecutive',
        'folio',
        'unique_identifier',
        'full_url',
        'user_id'
    ];
}