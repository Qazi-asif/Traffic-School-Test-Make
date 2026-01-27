<?php

namespace App\Http\Controllers;

use App\Models\DicdsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class WebAdminController extends Controller
{
    public function index()
    {
        return view('dicds.admin.index');
    }

    public function userRoleAdmin()
    {
        return view('dicds.admin.user-role-admin');
    }

    public function searchUsers(Request $request)
    {
        $users = DicdsUser::when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->role, fn ($q) => $q->where('desired_role', $request->role))
            ->get();

        return view('dicds.admin.search-results', compact('users'));
    }

    public function showUser($id)
    {
        $user = DicdsUser::findOrFail($id);

        return view('dicds.admin.user-account', compact('user'));
    }

    public function updateUserStatus(Request $request, $id)
    {
        $user = DicdsUser::findOrFail($id);
        $user->update(['status' => $request->status]);

        if ($request->send_email) {
            // Send email notification
            Mail::raw("Your account status has been updated to: {$request->status}", function ($message) use ($user) {
                $message->to($user->contact_email)->subject('DICDS Account Status Update');
            });
        }

        return back()->with('success', 'User status updated successfully');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = DicdsUser::findOrFail($id);
        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password reset successfully');
    }

    public function updateUserRole(Request $request, $id)
    {
        $user = DicdsUser::findOrFail($id);
        $user->update([
            'desired_application' => $request->desired_application,
            'desired_role' => $request->desired_role,
            'user_group' => $request->user_group,
        ]);

        return back()->with('success', 'User role updated successfully');
    }
}
