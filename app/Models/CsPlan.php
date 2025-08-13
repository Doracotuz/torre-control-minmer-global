<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class CsPlan extends Model {
    use HasFactory;
    protected $table = 'cs_plans';
    protected $guarded = [];
    public function order() { return $this->belongsTo(CsOrder::class); }
}