<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If user doesn't have 2FA enabled, continue normally
        if (!$user || !$user->two_factor_enabled) {
            return $next($request);
        }

        // Check if 2FA is already verified for this session
        if (session('two_factor_verified')) {
            // Check if verification is still valid (24 hours)
            $verifiedAt = $user->two_factor_verified_at;
            if ($verifiedAt && $verifiedAt->addHours(24) > now()) {
                return $next($request);
            } else {
                // Clear expired verification
                session()->forget('two_factor_verified');
            }
        }

        // If this is an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Two-factor authentication required',
                'requires_2fa' => true
            ], 403);
        }

        // Redirect to 2FA verification page
        return redirect()->route('two-factor.verify');
    }
}