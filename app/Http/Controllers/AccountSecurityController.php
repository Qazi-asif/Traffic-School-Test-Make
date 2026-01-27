<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\SecurityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountSecurityController extends Controller
{
    public function getSecuritySettings(): JsonResponse
    {
        \Log::info('AccountSecurityController getSecuritySettings called', [
            'user_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? null
        ]);

        if (! auth()->check()) {
            \Log::error('User not authenticated in getSecuritySettings');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();

        \Log::info('Returning security settings', [
            'user_id' => $user->id,
            'two_factor_enabled' => $user->two_factor_enabled ?? false
        ]);

        return response()->json([
            'two_factor_enabled' => $user->two_factor_enabled ?? false,
            'last_password_change' => $user->updated_at,
            'active_sessions' => 1, // Placeholder
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 422);
        }

        // Update password
        $user->update(['password' => Hash::make($request->new_password)]);

        // Log security event (if SecurityLog exists)
        try {
            SecurityLog::create([
                'user_id' => $user->id,
                'event_type' => 'password_change',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => 'Password changed successfully',
                'risk_level' => 'low',
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // SecurityLog table may not exist, continue anyway
        }

        return response()->json(['message' => 'Password changed successfully']);
    }

    public function getLoginHistory(): JsonResponse
    {
        \Log::info('AccountSecurityController getLoginHistory called', [
            'user_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? null
        ]);

        if (! auth()->check()) {
            \Log::error('User not authenticated in getLoginHistory');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $attempts = LoginAttempt::where('email', auth()->user()->email)
            ->orderBy('attempted_at', 'desc')
            ->limit(50)
            ->get();

        \Log::info('Returning login history', [
            'user_id' => auth()->id(),
            'attempts_count' => $attempts->count()
        ]);

        return response()->json($attempts);
    }
}
