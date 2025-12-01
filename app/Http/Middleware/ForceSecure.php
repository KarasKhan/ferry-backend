<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceSecure
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // PENTING: Cek jika APP_ENV adalah production.
        // Jika iya, paksa request dianggap aman (HTTPS).
        if (config('app.env') === 'production') {
            $request->setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
            $request->server->set('HTTPS', 'on');
            $request->setSecure(true);
        }

        return $next($request);
    }
}