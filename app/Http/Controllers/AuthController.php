<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'role_id' => $request->role_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user->load('role'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user exists and is not locked
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->account_locked) {
            return back()->withErrors([
                'email' => 'Your account has been locked. Please contact support to regain access.',
            ])->onlyInput('email');
        }

        // Check if this is a web request (expects HTML) or API request (expects JSON)
        if ($request->expectsJson()) {
            $success = (bool) JWTAuth::attempt($credentials);

            // Log attempt
            \App\Models\LoginAttempt::create([
                'email' => $credentials['email'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => $success,
                'attempted_at' => now(),
            ]);

            if (! $success) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();

            // Check if 2FA is enabled for API requests
            if ($user->two_factor_enabled) {
                // For API requests, return that 2FA is required
                return response()->json([
                    'requires_2fa' => true,
                    'message' => 'Two-factor authentication required'
                ], 200);
            }

            return response()->json([
                'user' => $user->load('role'),
                'token' => JWTAuth::attempt($credentials),
            ]);
        }

        // Web login with session
        $success = auth()->attempt($credentials, $request->filled('remember'));

        // Log attempt
        \App\Models\LoginAttempt::create([
            'email' => $credentials['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => $success,
            'attempted_at' => now(),
        ]);

        if ($success) {
            $user = auth()->user();
            
            // Check if 2FA is enabled
            if ($user->two_factor_enabled) {
                // Generate and send 2FA code
                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                
                $user->update([
                    'two_factor_code' => Hash::make($code),
                    'two_factor_expires_at' => now()->addMinutes(10),
                    'two_factor_verified_at' => null,
                    'two_factor_attempts' => 0
                ]);

                // Send email with code
                try {
                    \Mail::send('emails.two-factor-code', [
                        'user' => $user,
                        'code' => $code
                    ], function ($message) use ($user) {
                        $message->to($user->email)
                                ->subject('Your Two-Factor Authentication Code');
                    });
                } catch (\Exception $e) {
                    \Log::error('Failed to send 2FA code during login: ' . $e->getMessage());
                    return back()->withErrors([
                        'email' => 'Failed to send verification code. Please try again.',
                    ])->onlyInput('email');
                }

                // Store user ID in session for 2FA verification
                session(['pending_2fa_user_id' => $user->id]);
                
                // Logout the user temporarily until 2FA is verified
                auth()->logout();
                
                // Redirect to 2FA verification page
                return redirect()->route('two-factor.verify')->with('message', 'Please check your email for the verification code.');
            }

            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Successfully logged out']);
        }

        // Web logout
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function user()
    {
        return response()->json(auth()->user()->load('role'));
    }

    public function userWeb()
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            // Load the role relationship
            $user->load('role');

            return response()->json($user);
        } catch (\Exception $e) {
            \Log::error('Error in userWeb: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function updateProfileWeb(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                // Basic Information
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,'.$user->id,
                'phone' => 'sometimes|string|max:20',
                'address' => 'sometimes|string',
                'city' => 'sometimes|string',
                'state' => 'sometimes|string',
                'zip_code' => 'sometimes|string',
                
                // License Information
                'license_state' => 'sometimes|string|max:255',
                'license_class' => 'sometimes|string|max:255',
                
                // Court & Citation Information
                'court_selected' => 'sometimes|string|max:255',
                'citation_number' => 'sometimes|string|max:255',
                'due_month' => 'sometimes|integer|min:1|max:12',
                'due_day' => 'sometimes|integer|min:1|max:31',
                'due_year' => 'sometimes|integer|min:2020|max:2030',
                
                // Security Questions
                'security_q1' => 'sometimes|string|max:255',
                'security_q2' => 'sometimes|string|max:255',
                'security_q3' => 'sometimes|string|max:255',
                'security_q4' => 'sometimes|string|max:255',
                'security_q5' => 'sometimes|string|max:255',
                'security_q6' => 'sometimes|string|max:255',
                'security_q7' => 'sometimes|string|max:255',
                'security_q8' => 'sometimes|string|max:255',
                'security_q9' => 'sometimes|string|max:255',
                'security_q10' => 'sometimes|string|max:255',
                
                // Agreement Information
                'agreement_name' => 'sometimes|string|max:255',
                'terms_agreement' => 'sometimes|boolean',
            ]);

            $user->update($validated);

            return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
