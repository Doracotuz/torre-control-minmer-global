<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToArea;
class FfTransportLine extends Model {
    use BelongsToArea;
    protected $table = 'ff_transport_lines';
    protected $fillable = ['name', 'is_active', 'area_id'];
}