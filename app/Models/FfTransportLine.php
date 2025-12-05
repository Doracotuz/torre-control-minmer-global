<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FfTransportLine extends Model {
    protected $table = 'ff_transport_lines';
    protected $fillable = ['name', 'is_active'];
}