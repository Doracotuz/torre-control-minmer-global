<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptEvidence extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'receipt_evidences';

    /**
     * @var array<int, string>
     */
    protected $fillable = ['purchase_order_id', 'user_id', 'type', 'file_path', 'original_name'];
}