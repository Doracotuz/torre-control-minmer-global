<?php

namespace App\Models\Tms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'tms_invoices';
    protected $fillable = ['shipment_id', 'invoice_number', 'box_quantity', 'bottle_quantity', 'status'];

    // Una factura pertenece a un embarque (guia)
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    // Una factura puede tener muchas fotos (polimórfico)
    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }
}
