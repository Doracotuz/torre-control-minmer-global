<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'from_location_id', 'to_location_id', 'quantity', 'user_id', 'notes'];
}