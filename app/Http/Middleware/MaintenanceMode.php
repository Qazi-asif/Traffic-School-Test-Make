<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        $downFile = storage_path('framework/down');
        
        if (!file_exists($downFile)) {
            return $next($request);
        }
        
        $data = json_decode(file_get_contents($downFile), true);
        $allow = $data['allow'] ?? [];
        $path = $request->getPathInfo();
        
        // Check if path is in allow list (paths start with /)
        foreach ($allow as $allowedPath) {
            if (strpos($allowedPath, '/') === 0 && strpos($path, $allowedPath) === 0) {
                return $next($request);
            }
        }
        
        // Show maintenance page
        return response()->view('maintenance', [
            'message' => $data['message'] ?? 'Site is under maintenance.'
        ], 503);
    }
}

