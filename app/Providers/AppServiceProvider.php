<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
        Model::unguard();

        // <--- TAMBAHKAN BLOK INI
        // Paksa HTTPS jika di production (Railway)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            
            // Opsional: Paksa asset root ke HTTPS juga
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}