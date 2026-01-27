<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CopyrightProtection
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add headers to prevent caching of course content
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // Prevent iframe embedding
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Add CSP headers
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'");

        return $response;
    }
}
