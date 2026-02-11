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
                    $model = $builder->getModel();
                    if (property_exists($model, 'allowsGlobalArea') && $model->allowsGlobalArea) {
                        $builder->where(function($q) use ($user, $model) {
                            $q->where($model->getTable() . '.area_id', $user->area_id)
                              ->orWhereNull($model->getTable() . '.area_id');
                        });
                    } else {
                        $builder->where($builder->getModel()->getTable() . '.area_id', $user->area_id);
                    }
                }
            }
        });

        static::creating(function ($model) {
            if (Auth::check() && !array_key_exists('area_id', $model->getAttributes())) {
                $model->area_id = Auth::user()->area_id;
            }
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}