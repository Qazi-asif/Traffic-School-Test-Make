<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StateMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $state): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super admins have access to all states
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check if admin has access to the requested state
        if (!$admin->hasStateAccess($state)) {
            abort(403, "You don't have access to {$state} administration.");
        }

        // Add state to request for easy access in controllers
        $request->merge(['current_state' => $state]);

        return $next($request);
    }
}