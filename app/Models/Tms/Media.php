<?php

namespace App\Models\Tms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    protected $table = 'tms_media';
    protected $fillable = ['model_type', 'model_id', 'file_path', 'collection_name'];

    // Relación polimórfica para obtener el modelo padre (evento, factura, etc.)
    public function model()
    {
        return $this->morphTo();
    }
}
