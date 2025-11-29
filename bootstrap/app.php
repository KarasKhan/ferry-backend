<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <--- Pastikan import ini ada

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // --- TAMBAHKAN BAGIAN INI ---
        $middleware->trustProxies(at: '*'); 
        // Artinya: Percaya pada semua proxy (termasuk Railway)
        // ----------------------------

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();