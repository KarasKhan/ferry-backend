<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceSecure
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan ini hanya berjalan di Production (Railway)
        if (config('app.env') === 'production') {
            
            // 1. Set Trusted Proxies
            // Menggunakan method yang benar untuk mengatur proxy.
            $request->setTrustedProxies(
                ['*'], 
                Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
            );

            // 2. Memaksa server untuk melihat koneksi sebagai HTTPS
            // Ini adalah metode yang benar untuk memanipulasi SERVER array.
            $request->server->set('HTTPS', 'on'); 
            
            // CATATAN: Method $request->setSecure(true); DIHAPUS.
        }

        return $next($request);
    }
}