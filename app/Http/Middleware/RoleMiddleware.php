<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect('/login');
        }

        $user = auth()->user();
        
        // If no roles specified, just check authentication
        if (empty($roles)) {
            return $next($request);
        }

        // Get user role (handle both string and object cases)
        $userRole = is_string($user->role) ? $user->role : ($user->role->slug ?? 'user');
        
        // Allow super-admin to access everything
        if ($userRole === 'super-admin') {
            return $next($request);
        }
        
        // Check if user's role is in the allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // User doesn't have required role
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }
        
        return redirect('/dashboard')->with('error', 'You do not have permission to access this page.');
    }
}