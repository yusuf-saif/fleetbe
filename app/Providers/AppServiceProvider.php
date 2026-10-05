<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::define('viewScalar', function ($user = null) {
            return true;
        });

        Relation::morphMap([
            'fuel' => \App\Models\Fuel::class,
            'spare_part' => \App\Models\SparePart::class,
            'maintenance' => \App\Models\Maintenance::class,
        ]);
    }
}
