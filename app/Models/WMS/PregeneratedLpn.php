<?php
namespace App\Models\WMS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PregeneratedLpn extends Model {
    use HasFactory;
    protected $fillable = ['lpn', 'is_used'];
}