<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Model;

class InboundReceipt extends Model
{
    protected $fillable = ['purchase_order_id', 'user_id', 'notes', 'container_number', 'pedimento_a4', 'pedimento_g1', 'document_invoice'];
}
