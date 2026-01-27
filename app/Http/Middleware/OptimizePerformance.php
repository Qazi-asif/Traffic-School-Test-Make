<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OptimizePerformance
{
    public function handle(Request $request, Closure $next)
    {
        // Enable query caching for GET requests
        if ($request->isMethod('GET')) {
            DB::enableQueryLog();
        }

        // Set response headers for caching
        $response = $next($request);

        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            // Cache API responses for 5 minutes
            if ($request->is('api/*')) {
                $response->headers->set('Cache-Control', 'public, max-age=300');
            }
            
            // Cache static content for 1 hour
            if ($request->is('css/*') || $request->is('js/*') || $request->is('images/*')) {
                $response->headers->set('Cache-Control', 'public, max-age=3600');
            }
        }

        return $response;
    }
}