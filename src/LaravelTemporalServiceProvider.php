<?php

namespace Bloomlive\LaravelTemporal;

use Illuminate\Database\Schema\Blueprint;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTemporalServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        Blueprint::macro('temporal', function ($fromColumn = null, $toColumn = null) {
            $this->timestamp($fromColumn ?: config('temporal.database.from_column'));
            $this->timestamp($toColumn ?: config('temporal.database.to_column'));
        });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-temporal')
            ->hasConfigFile();
    }
}
