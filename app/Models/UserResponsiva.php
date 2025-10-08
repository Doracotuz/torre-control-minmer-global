<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserResponsiva extends Model
{
    use HasFactory;
    protected $fillable = ['organigram_member_id', 'file_path', 'generated_date'];
}