<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectronicLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'label_type',
        'series',
        'consecutive',
        'folio',
        'unique_identifier',
        'full_url',
        'elaboration_date',
        'label_batch',
        'product_name',
        'product_type',
        'alcohol_content',
        'capacity',
        'origin',
        'packaging_date',
        'product_batch',
        'maker_name',
        'maker_rfc',
        'user_id'
    ];
    
    protected $casts = [
        'elaboration_date' => 'date',
        'packaging_date' => 'date',
        'alcohol_content' => 'decimal:1',
    ];
}