<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DRMProtectionHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only apply to course-player routes
        if (!$request->is('course-player*')) {
            return $response;
        }

        // Prevent caching
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');

        // Prevent framing
        $response->header('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->header('X-Content-Type-Options', 'nosniff');

        // XSS protection
        $response->header('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->header('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
