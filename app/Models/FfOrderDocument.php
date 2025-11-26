<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FfOrderDocument extends Model
{
    protected $fillable = ['folio', 'filename', 'path'];

    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->path);
    }
}