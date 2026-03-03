<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('layouts.metronic', function ($view) {
            $currentFamily = null;
            if (auth()->check()) {
                $currentFamily = auth()->user()->families()->first();
            }
            $view->with('currentFamily', $currentFamily);
        });
    }
}
