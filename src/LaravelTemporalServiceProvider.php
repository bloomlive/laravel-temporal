<?php

namespace Bloomlive\LaravelTemporal;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class LaravelTemporalServiceProvider extends ServiceProvider
{
    public function register()
    {
        Blueprint::macro('temporal', function ($toColumn = null) {
            $this->timestamp($toColumn ?: config('temporal.database.to_column'))->nullable();
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/temporal.php',
            'temporal'
        );

        $this->publishes([
            __DIR__.'/config/temporal.php' => config_path('temporal.php'),
        ]);
    }
}
