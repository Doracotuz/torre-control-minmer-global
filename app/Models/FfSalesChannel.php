<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FfSalesChannel extends Model {
    protected $table = 'ff_sales_channels';
    protected $fillable = ['name', 'is_active'];
}