<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class CsOrderEvent extends Model {
    use HasFactory;
    protected $table = 'cs_order_events';
    protected $guarded = [];
    public function order() { return $this->belongsTo(CsOrder::class); }
    public function user() { return $this->belongsTo(User::class); }
}