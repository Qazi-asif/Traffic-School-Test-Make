<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect('/login')->with('error', 'Please login to access super admin area');
        }

        $user = Auth::user();

        // Check if user has super admin role (role_id 1 = Super Admin)
        if ($user->role_id !== 1) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden - Super Admin access required'], 403);
            }
            return redirect('/dashboard')->with('error', 'Super Admin access required');
        }

        // Log super admin access for security audit
        \Log::info('Super Admin access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()->getName(),
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);

        return $next($request);
    }
}