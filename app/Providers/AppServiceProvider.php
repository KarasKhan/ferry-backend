<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- Pastikan ini di-import

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paksa semua Link/URL yang digenerate Laravel menjadi HTTPS di Production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}