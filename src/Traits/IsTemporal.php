<?php

namespace Bloomlive\LaravelTemporal\Traits;

use Bloomlive\LaravelTemporal\Exceptions\TemporalNotCurrentlyValidException;
use Bloomlive\LaravelTemporal\Scopes\TemporalCurrentlyValidScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

trait IsTemporal
{
    protected function getValidFromTimeColumn()
    {
        return config('temporal.database.from_column');
    }

    protected function getValidToTimeColumn()
    {
        return config('temporal.database.to_column');
    }

    public function getValidFromAttribute()
    {
        return $this->{$this->getValidFromTimeColumn()};
    }

    public function getValidToAttribute()
    {
        return $this->{$this->getValidToTimeColumn()};
    }

    public function scopeFuture(Builder $query, Model $model): Builder {
        return $query->where($model->valid_from, '>', now());
    }

    public function scopePast(Builder $query, Model $model): Builder {
        return $query->where($model->valid_to, '<', now());
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
        return ($this->{$this->getValidFromTimeColumn()} >= now() && $this->{$this->getValidToTimeColumn()} <= now());
    }

    public function invalidate(Carbon $from = null): bool {
        if (!$from instanceof Carbon) {
            $this->invalidateCurrent();
        }

        if ($this->fireModelEvent('invalidating') === false) {
            return false;
        }

        $this->{$this->getValidToTimeColumn()} = $from;

        $result = $this->save();

        $this->fireModelEvent('invalidated', false);

        return $result;
    }

    private function invalidateCurrent() {
        if (!$this->isCurrentlyValid()) {
            throw new TemporalNotCurrentlyValidException('');
        }

        if ($this->fireModelEvent('invalidating') === false) {
            return false;
        }

        $this->{$this->getValidToTimeColumn()} = now();

        $result = $this->save();

        $this->fireModelEvent('invalidated', false);

        return $result;
    }

    public function mergeCasts($casts)
    {
        $this->casts = array_merge([
            $this->getValidFromTimeColumn() => 'timestamp',
            $this->getValidToTimeColumn() => 'timestamp',
        ], $casts);

        return $this;
    }

    public static function bootIsTemporal()
    {
        static::addGlobalScope(TemporalCurrentlyValidScope::class);
    }

}
