<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToArea;
class FfPaymentCondition extends Model {
    use BelongsToArea;
    protected $table = 'ff_payment_conditions';
    protected $fillable = ['name', 'is_active', 'area_id'];
}