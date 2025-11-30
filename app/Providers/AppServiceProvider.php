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
        // if ($this->app->environment('production')) {     <-- KOMENTARI INI
        //    \Illuminate\Support\Facades\URL::forceScheme('https');
        //    $this->app['request']->server->set('HTTPS', 'on');
        // }
        
        // Ganti dengan yang lebih soft:
        if($this->app->environment('production')) {
             \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}