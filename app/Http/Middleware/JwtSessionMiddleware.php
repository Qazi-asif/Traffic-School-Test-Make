<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtSessionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Try to get token from session
        $token = session('jwt_token');

        \Log::info('JwtSessionMiddleware check', [
            'has_token' => ! empty($token),
            'token' => $token,
            'session_id' => session()->getId(),
        ]);

        if ($token) {
            try {
                // Set the token and get the user
                JWTAuth::setToken($token);
                $user = JWTAuth::authenticate();

                // If user found, set the authenticated user
                if ($user) {
                    auth()->setUser($user);
                    \Log::info('User authenticated via JWT', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                } else {
                    \Log::info('JWT token valid but no user found');
                }
            } catch (\Exception $e) {
                // If token is invalid, remove it from session
                session()->forget('jwt_token');
                \Log::info('Invalid JWT token, removed from session', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }
}
