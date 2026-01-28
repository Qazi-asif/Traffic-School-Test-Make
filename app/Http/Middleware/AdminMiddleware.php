<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect('/login')->with('error', 'Please login to access admin area');
        }

        $user = Auth::user();

        // Check if user has admin role (role_id 1 = Super Admin, 2 = School Admin)
        if (!in_array($user->role_id, [1, 2])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden - Admin access required'], 403);
            }
            return redirect('/dashboard')->with('error', 'Admin access required');
        }

        // Log admin access for security audit
        \Log::info('Admin access', [
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