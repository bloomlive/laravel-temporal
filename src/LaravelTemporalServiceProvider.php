<?php

namespace Bloomlive\LaravelTemporal;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class LaravelTemporalServiceProvider extends ServiceProvider
{
    public function register()
    {
        Blueprint::macro('temporal', function ($fromColumn = null, $toColumn = null) {
            $this->timestamp($fromColumn ?: config('temporal.database.from_column'));
            $this->timestamp($toColumn ?: config('temporal.database.to_column'));
        });
    }

    public function configurePackage(): void
    {
        $this->publishes([
            __DIR__ . '/config/temporal.php' => config_path('temporal.php'),
        ]);
    }
}
