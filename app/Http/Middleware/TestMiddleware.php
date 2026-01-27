<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Force log to see if ANY middleware is running
        error_log("🚀 TestMiddleware is running for: " . $request->path());
        
        // Add a header to prove middleware ran
        $response = $next($request);
        $response->headers->set('X-Test-Middleware', 'executed');
        
        return $response;
    }
}