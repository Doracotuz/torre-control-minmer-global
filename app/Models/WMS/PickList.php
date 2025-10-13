<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickList extends Model {
    use HasFactory;
    protected $fillable = ['sales_order_id', 'user_id', 'picker_id', 'status'];
    public function salesOrder() { return $this->belongsTo(SalesOrder::class); }
    public function items() { return $this->hasMany(PickListItem::class); }
}