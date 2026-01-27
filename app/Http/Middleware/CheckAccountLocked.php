<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAccountLocked
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->account_locked) {
            auth()->logout();

            return redirect('/login')->with('error', 'Your account has been locked. Please contact support to regain access.');
        }

        return $next($request);
    }
}
