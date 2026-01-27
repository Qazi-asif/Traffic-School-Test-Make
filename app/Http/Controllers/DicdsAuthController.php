<?php

namespace App\Http\Controllers;

use App\Models\DicdsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DicdsAuthController extends Controller
{
    public function showLogin()
    {
        return view('dicds.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DicdsUser::where('login_id', $request->login_id)->first();

        if ($user && Hash::check($request->password, $user->password) && $user->status === 'Active') {
            auth('dicds')->login($user);

            return redirect()->route('dicds.main-menu');
        }

        return back()->withErrors(['login_id' => 'Invalid credentials or account not active']);
    }

    public function showRegister()
    {
        return view('dicds.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'user_last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'contact_email' => 'required|email|confirmed',
            'phone_number' => 'required|string',
            'login_id' => 'required|string|unique:dicds_users',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ]);

        DicdsUser::create([
            'user_last_name' => $request->user_last_name,
            'first_name' => $request->first_name,
            'middle' => $request->middle,
            'suffix' => $request->suffix,
            'contact_email' => $request->contact_email,
            'retype_email' => $request->contact_email_confirmation,
            'phone_number' => $request->phone_number,
            'phone_extension' => $request->phone_extension,
            'login_id' => $request->login_id,
            'password' => $request->password,
            'status' => 'Pending',
        ]);

        return redirect()->route('dicds.access-request');
    }

    public function showAccessRequest()
    {
        return view('dicds.access-request');
    }

    public function accessRequest(Request $request)
    {
        $request->validate([
            'desired_application' => 'required|string',
            'desired_role' => 'required|in:DRS_Provider_Admin,DRS_Provider_User,DRS_School_Admin',
            'user_group' => 'required|string',
        ]);

        $user = DicdsUser::where('login_id', session('temp_login_id'))->first();
        $user->update($request->only(['desired_application', 'desired_role', 'user_group']));

        return view('dicds.request-submitted');
    }

    public function logout()
    {
        auth('dicds')->logout();

        return redirect()->route('dicds.login');
    }
}
