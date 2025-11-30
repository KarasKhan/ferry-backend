<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Deteksi jika di Production (Railway)
        if ($this->app->environment('production')) {
            // 1. Paksa URL Generator pakai HTTPS
            URL::forceScheme('https');
            
            // 2. Paksa Request Object sadar HTTPS (Penting buat Livewire)
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}