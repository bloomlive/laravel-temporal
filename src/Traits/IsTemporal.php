<?php

namespace Bloomlive\LaravelTemporal\Traits;

use Bloomlive\LaravelTemporal\Exceptions\TemporalNotCurrentlyValidException;
use Bloomlive\LaravelTemporal\Scopes\TemporalCurrentlyValidScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

trait IsTemporal
{
    abstract public function secondaryKey(): string;

    public function getValidToTimeColumn()
    {
        return config('temporal.database.to_column');
    }

    public function scopePast(Builder $query, Model $model): Builder
    {
        return $query->where($model->attributes[$this->getValidToTimeColumn()], '<', now());
    }

    public static function invalidated($callback)
    {
        static::registerModelEvent('invalidated', $callback);
    }

    public static function invalidating($callback)
    {
        static::registerModelEvent('invalidating', $callback);
    }

    public function isCurrentlyValid(): bool
    {
        return ($this->attributes[$this->getValidToTimeColumn()] <= now() || $this->attributes[$this->getValidToTimeColumn()] === null);
    }

    public function scopeWasValidAt(Builder $query, Carbon $timestamp)
    {
        return $query->withoutGlobalScope(TemporalCurrentlyValidScope::class)
            ->where(function ($query) use ($timestamp) {
                $query->where($this->getValidToTimeColumn(), '>=', $timestamp)
                    ->orWhere($this->getValidToTimeColumn(), '=', null);
            })
            ->orderByDesc($this->getValidToTimeColumn())
            ->limit(1);
    }

    public function invalidate(Carbon $since = null)
    {
        if (!$this->isCurrentlyValid()) {
            throw new TemporalNotCurrentlyValidException();
        }

        if ($this->fireModelEvent('invalidating') === false) {
            return false;
        }

        $this->attributes[$this->getValidToTimeColumn()] = $since ?: \Illuminate\Support\Carbon::now();

        $result = $this->save();

        $this->fireModelEvent('invalidated', false);

        return $result;
    }

    public function mergeCasts($casts)
    {
        $this->casts = array_merge([
            $this->getValidToTimeColumn() => 'datetime',
        ], $casts);

        return $this;
    }

    public static function bootIsTemporal()
    {
        static::addGlobalScope(new \Bloomlive\LaravelTemporal\Scopes\TemporalCurrentlyValidScope);

        static::creating(function(self $model) {
            $current = $model->where($model->secondaryKey(), '=', $model[$model->secondaryKey()])->first();

            $current?->invalidate($model->created_at);
        });
    }
}
