<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'role_id' => $request->role_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'driver_license' => $request->driver_license,
            'dicds_user_id' => $request->dicds_user_id,
            'dicds_password' => $request->dicds_password ? encrypt($request->dicds_password) : null,
            'status' => 'active',
        ]);

        return response()->json($user->load('role'), 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $data = $request->only(['role_id', 'first_name', 'last_name', 'email', 'phone', 'address', 'driver_license', 'dicds_user_id', 'status']);

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->dicds_password) {
            $data['dicds_password'] = encrypt($request->dicds_password);
        }

        $user->update($data);

        return response()->json($user->load('role'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // Web methods for session-based authentication
    public function indexWeb(Request $request)
    {
        try {
            $query = User::with('role');

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('first_name', 'like', "%{$request->search}%")
                        ->orWhere('last_name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            }

            if ($request->role_id) {
                $query->where('role_id', $request->role_id);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Allow custom per_page parameter, default to 15, max 100
            $perPage = min($request->get('per_page', 15), 100);
            $result = $query->paginate($perPage);

            return response()->json([
                'data' => $result->items(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'total' => $result->total(),
                'per_page' => $result->perPage(),
            ]);
        } catch (\Exception $e) {
            \Log::error('UserController indexWeb error: '.$e->getMessage());

            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0,
                'error' => 'Failed to load users',
            ], 500);
        }
    }

    public function storeWeb(Request $request)
    {
        return $this->store($request);
    }

    public function updateWeb(Request $request, User $user)
    {
        return $this->update($request, $user);
    }

    public function destroyWeb(User $user)
    {
        return $this->destroy($user);
    }
}
