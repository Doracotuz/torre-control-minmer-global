<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FfOrderEvidence extends Model
{
    protected $table = 'ff_order_evidences'; 

    protected $fillable = ['folio', 'filename', 'path', 'uploaded_by'];

    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->path);
    }
    
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}