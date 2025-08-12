<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsWarehouse extends Model
{
    use HasFactory;

    protected $table = 'cs_warehouses';

    protected $fillable = [
        'warehouse_id',
        'name',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
