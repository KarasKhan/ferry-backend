<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- JANGAN LUPA IMPORT INI

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
        // Deteksi jika di Production (Railway)
        if ($this->app->environment('production')) {
            // 1. Paksa Generator URL pakai HTTPS
            \Illuminate\Support\Facades\URL::forceScheme('https');
            
            // 2. Paksa Objek Request menganggap dirinya HTTPS (PENTING BUAT LIVEWIRE)
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}