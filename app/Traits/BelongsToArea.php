<?php

namespace App\Traits;

use App\Models\Area;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToArea
{
    protected static function bootBelongsToArea()
    {
        static::addGlobalScope('area', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                if (!$user->isSuperAdmin()) {
                    $builder->where($builder->getModel()->getTable() . '.area_id', $user->area_id);
                }
            }
        });

        static::creating(function ($model) {
            if (Auth::check() && !isset($model->area_id)) {
                $model->area_id = Auth::user()->area_id;
            }
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}