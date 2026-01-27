<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserAccessController extends Controller
{
    public function index()
    {
        $lockedUsers = User::where('account_locked', true)
            ->orderBy('locked_at', 'desc')
            ->paginate(20);

        return view('admin.user-access', compact('lockedUsers'));
    }

    public function unlock(User $user)
    {
        $user->update([
            'account_locked' => false,
            'lock_reason' => null,
            'locked_at' => null,
        ]);

        return redirect()->back()->with('success', 'User account unlocked successfully');
    }

    public function api()
    {
        $lockedUsers = User::where('account_locked', true)
            ->select('id', 'first_name', 'last_name', 'email', 'lock_reason', 'locked_at')
            ->orderBy('locked_at', 'desc')
            ->get();

        return response()->json($lockedUsers);
    }

    public function apiUnlock(User $user)
    {
        $user->update([
            'account_locked' => false,
            'lock_reason' => null,
            'locked_at' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'User unlocked']);
    }
}
