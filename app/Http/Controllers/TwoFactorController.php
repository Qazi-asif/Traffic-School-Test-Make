<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        \Log::info('TwoFactorController enable method called', ['request' => $request->all()]);
        
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        \Log::info('Current user', ['user_id' => $user ? $user->id : 'null']);

        if (!$user) {
            \Log::error('No authenticated user found');
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            \Log::error('Invalid password provided for 2FA enable');
            return response()->json(['error' => 'Invalid password'], 400);
        }

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_verified_at' => null,
            'two_factor_attempts' => 0
        ]);

        \Log::info('2FA enabled successfully for user', ['user_id' => $user->id]);

        return response()->json(['message' => 'Two-factor authentication enabled successfully']);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid password'], 400);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_verified_at' => null,
            'two_factor_attempts' => 0
        ]);

        return response()->json(['message' => 'Two-factor authentication disabled successfully']);
    }

    public function sendCode(Request $request)
    {
        // Check if this is during login process
        $pendingUserId = session('pending_2fa_user_id');
        $user = null;

        if ($pendingUserId) {
            // This is during login - get user from session
            $user = User::find($pendingUserId);
        } else {
            // This is for already authenticated user
            $user = Auth::user();
        }

        if (!$user) {
            return response()->json(['error' => 'No user found for code generation'], 400);
        }

        if (!$user->two_factor_enabled) {
            return response()->json(['error' => 'Two-factor authentication is not enabled'], 400);
        }

        // Check if too many attempts
        if ($user->two_factor_attempts >= 5) {
            $lastAttempt = $user->two_factor_expires_at;
            if ($lastAttempt && $lastAttempt->addMinutes(15) > now()) {
                return response()->json([
                    'error' => 'Too many attempts. Please try again in 15 minutes.'
                ], 429);
            } else {
                // Reset attempts after cooldown
                $user->update(['two_factor_attempts' => 0]);
            }
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'two_factor_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(10),
            'two_factor_verified_at' => null
        ]);

        // Send email with code
        try {
            Mail::send('emails.two-factor-code', [
                'user' => $user,
                'code' => $code
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Your Two-Factor Authentication Code');
            });

            return response()->json([
                'message' => 'Verification code sent to your email',
                'expires_in' => 10 // minutes
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send 2FA code: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send verification code'], 500);
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        // Check if this is during login process
        $pendingUserId = session('pending_2fa_user_id');
        $user = null;

        if ($pendingUserId) {
            // This is during login - get user from session
            $user = User::find($pendingUserId);
        } else {
            // This is for already authenticated user
            $user = Auth::user();
        }

        if (!$user) {
            return response()->json(['error' => 'No user found for verification'], 400);
        }

        if (!$user->two_factor_enabled) {
            return response()->json(['error' => 'Two-factor authentication is not enabled'], 400);
        }

        if (!$user->two_factor_code || !$user->two_factor_expires_at) {
            return response()->json(['error' => 'No verification code found. Please request a new code.'], 400);
        }

        if (now() > $user->two_factor_expires_at) {
            return response()->json(['error' => 'Verification code has expired. Please request a new code.'], 400);
        }

        if (!Hash::check($request->code, $user->two_factor_code)) {
            $user->increment('two_factor_attempts');
            
            $attemptsLeft = 5 - $user->two_factor_attempts;
            if ($attemptsLeft <= 0) {
                return response()->json([
                    'error' => 'Too many failed attempts. Please try again in 15 minutes.'
                ], 429);
            }

            return response()->json([
                'error' => "Invalid verification code. {$attemptsLeft} attempts remaining."
            ], 400);
        }

        // Code is valid
        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_verified_at' => now(),
            'two_factor_attempts' => 0
        ]);

        // If this was during login process, complete the login
        if ($pendingUserId) {
            // Log the user in
            Auth::login($user);
            
            // Clear the pending user ID from session
            session()->forget('pending_2fa_user_id');
            
            // Store in session that 2FA is verified for this login
            session(['two_factor_verified' => true]);
            
            return response()->json([
                'message' => 'Two-factor authentication verified successfully',
                'redirect' => '/dashboard'
            ]);
        } else {
            // Store in session that 2FA is verified for this login
            session(['two_factor_verified' => true]);
            
            return response()->json(['message' => 'Two-factor authentication verified successfully']);
        }
    }

    public function getStatus()
    {
        // Check if this is during login process
        $pendingUserId = session('pending_2fa_user_id');
        $user = null;

        if ($pendingUserId) {
            // This is during login - get user from session
            $user = User::find($pendingUserId);
        } else {
            // This is for already authenticated user
            $user = Auth::user();
        }

        if (!$user) {
            return response()->json(['error' => 'No user found'], 400);
        }
        
        return response()->json([
            'two_factor_enabled' => $user->two_factor_enabled,
            'code_sent' => $user->two_factor_code ? true : false,
            'code_expires_at' => $user->two_factor_expires_at,
            'verified_at' => $user->two_factor_verified_at,
            'attempts_remaining' => max(0, 5 - $user->two_factor_attempts)
        ]);
    }
}