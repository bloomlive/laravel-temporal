<?php

namespace Bloomlive\LaravelTemporal\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TemporalCurrentlyValidScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder
            ->where($model->{$model->getValidFromTimeColumn()}, '<=', now())
            ->where($model->{$model->getValidToTimeColumn()}, '>=', now());
    }
}
