<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DicdsUserManagementController extends Controller
{
    public function getUsers(Request $request)
    {
        $query = User::with('role');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $users = $query->get();

        return response()->json(['data' => $users]);
    }

    public function getRoles()
    {
        $roles = Role::all();

        return response()->json(['data' => $roles]);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role_id = $request->role_id;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $tempPassword = Str::random(12);
        $user->password = Hash::make($tempPassword);
        $user->save();

        return response()->json(['temp_password' => $tempPassword]);
    }
}
