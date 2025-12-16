<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToArea;

class FfSalesChannel extends Model {
    use BelongsToArea;
    protected $table = 'ff_sales_channels';
    protected $fillable = ['name', 'is_active', 'area_id'];
}