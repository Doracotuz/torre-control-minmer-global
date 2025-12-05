<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FfPaymentCondition extends Model {
    protected $table = 'ff_payment_conditions';
    protected $fillable = ['name', 'is_active'];
}