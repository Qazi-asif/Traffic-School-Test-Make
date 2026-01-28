<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StateAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $requiredState
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $requiredState = null)
    {
        // If user is not authenticated, redirect to login
        if (!Auth::check()) {
            $state = $request->route('state') ?? $requiredState;
            if ($state && in_array($state, ['florida', 'missouri', 'texas', 'delaware'])) {
                return redirect()->route('auth.login.form', $state);
            }
            return redirect('/login');
        }

        $user = Auth::user();
        
        // Admin users can access any state
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Get the required state from route parameter or middleware parameter
        $requiredState = $request->route('state') ?? $requiredState;
        
        // If no state requirement, allow access
        if (!$requiredState) {
            return $next($request);
        }
        
        // Check if user's state matches required state
        if ($user->state !== $requiredState) {
            // Redirect to user's correct state portal
            $userState = $user->state;
            if (in_array($userState, ['florida', 'missouri', 'texas', 'delaware'])) {
                return redirect()->route("{$userState}.dashboard")
                    ->with('error', 'You can only access the ' . ucfirst($userState) . ' portal.');
            }
            
            // If user doesn't have a valid state, redirect to general login
            return redirect('/login')->with('error', 'Please contact support to verify your account.');
        }

        return $next($request);
    }
}