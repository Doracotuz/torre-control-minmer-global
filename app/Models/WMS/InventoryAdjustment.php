<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;
    protected $fillable = ['physical_count_task_id', 'product_id', 'location_id', 'quantity_adjusted', 'reason', 'user_id'];
}