<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip maintenance check for admin routes and API routes
        if ($request->is('admin/*') || $request->is('api/*') || $request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        // Skip for authenticated admin users - check role_id directly
        if (auth()->check()) {
            $user = auth()->user();
            // Check if user has admin role (role_id 1 or 2 for super-admin/admin)
            if ($user->role_id && in_array($user->role_id, [1, 2])) {
                return $next($request);
            }
            // Also check if user has role relationship with admin slug
            if ($user->role && in_array($user->role->slug, ['super-admin', 'admin'])) {
                return $next($request);
            }
        }

        try {
            // Check if maintenance mode is enabled
            if (Setting::isMaintenanceMode()) {
                $message = Setting::get('maintenance_message', 'Site is under maintenance. Please check back later.');
                
                // Return maintenance page
                return response()->view('maintenance', [
                    'message' => $message
                ], 503);
            }
        } catch (\Exception $e) {
            // If we can't check maintenance mode (e.g., database issues), continue normally
            \Log::warning('Could not check maintenance mode: ' . $e->getMessage());
        }

        return $next($request);
    }
}