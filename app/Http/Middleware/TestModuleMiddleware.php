<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestModuleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Log every request to see if middleware is running
        Log::info("🔍 TEST MIDDLEWARE RUNNING - Path: " . $request->path());
        
        // Simple test - block admin routes (using actual admin routes)
        if ($request->is('admin/dashboard') || 
            $request->is('admin/*') || 
            $request->is('dashboard')) {
            Log::info("🚫 BLOCKING ADMIN ACCESS - Path: " . $request->path());
            
            return response()->view('errors.module-disabled', [
                'module' => 'admin_panel',
                'title' => 'TEST: Admin Blocked'
            ], 503);
        }
        
        return $next($request);
    }
}