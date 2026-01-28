<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StateAuthController extends Controller
{
    /**
     * Show state-specific login form
     */
    public function showLoginForm($state)
    {
        $validStates = ['florida', 'missouri', 'texas', 'delaware'];
        
        if (!in_array($state, $validStates)) {
            abort(404);
        }
        
        return view('auth.state-login', compact('state'));
    }
    
    /**
     * Handle state-specific login
     */
    public function login(Request $request, $state)
    {
        $validStates = ['florida', 'missouri', 'texas', 'delaware'];
        
        if (!in_array($state, $validStates)) {
            abort(404);
        }
        
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
        
        // Attempt login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Redirect to state-specific dashboard
            return redirect()->route("{$state}.dashboard");
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
    
    /**
     * Show state-specific registration form
     */
    public function showRegistrationForm($state)
    {
        $validStates = ['florida', 'missouri', 'texas', 'delaware'];
        
        if (!in_array($state, $validStates)) {
            abort(404);
        }
        
        return view('auth.state-register', compact('state'));
    }
    
    /**
     * Handle state-specific registration
     */
    public function register(Request $request, $state)
    {
        $validStates = ['florida', 'missouri', 'texas', 'delaware'];
        
        if (!in_array($state, $validStates)) {
            abort(404);
        }
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'driver_license' => 'nullable|string|max:50',
            'state' => 'required|string|max:50',
            'citation_number' => 'nullable|string|max:100',
        ]);
        
        // Create user with state information
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'driver_license' => $validated['driver_license'] ?? null,
            'state' => $state, // Set the state based on registration portal
            'license_state' => $validated['state'],
            'citation_number' => $validated['citation_number'] ?? null,
            'status' => 'active',
            'role_id' => 1, // Default student role
        ]);
        
        // Log the user in
        Auth::login($user);
        
        // Redirect to state-specific dashboard
        return redirect()->route("{$state}.dashboard")->with('success', 'Registration successful! Welcome to ' . ucfirst($state) . ' Traffic School.');
    }
    
    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}