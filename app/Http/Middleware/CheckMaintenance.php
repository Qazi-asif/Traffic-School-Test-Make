<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        if (!file_exists(storage_path('framework/down'))) {
            return $next($request);
        }

        $path = $request->getPathInfo();
        
        // Allow admin and api routes
        if (strpos($path, '/admin') === 0 || strpos($path, '/api') === 0) {
            return $next($request);
        }
        
        // Allow maintenance control panel (Laravel version)
        if (strpos($path, '/admin-maintenance-cbfbvib4767436667gdgdggdgfgfdfghdgh') === 0) {
            return $next($request);
        }
        
        // Allow direct maintenance control (PHP file execution)
        if (strpos($path, '/maintenance-direct-cbfbvib4767436667gdgdggdgfgfdfghdgh') === 0) {
            return $next($request);
        }
        
        // Allow direct PHP files (maintenance admin panel)
        if (substr($path, -4) === '.php') {
            return $next($request);
        }

        return response()->view('maintenance', [], 503);
    }
}
