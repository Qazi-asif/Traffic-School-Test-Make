<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminUser::with(['creator'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $adminUsers = $query->paginate(20);

        $stats = [
            'total' => AdminUser::count(),
            'active' => AdminUser::where('is_active', true)->count(),
            'inactive' => AdminUser::where('is_active', false)->count(),
            'super_admins' => AdminUser::where('role', 'super_admin')->count(),
            'state_admins' => AdminUser::where('role', 'state_admin')->count(),
            'instructors' => AdminUser::where('role', 'instructor')->count(),
        ];

        return view('admin.admin-users.index', compact('adminUsers', 'stats'));
    }

    public function create()
    {
        $roles = [
            'super_admin' => 'Super Admin',
            'state_admin' => 'State Admin',
            'instructor' => 'Instructor',
        ];

        $states = ['florida', 'missouri', 'texas', 'delaware'];

        return view('admin.admin-users.create', compact('roles', 'states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,state_admin,instructor',
            'permissions' => 'nullable|array',
            'state_access' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $adminUser = AdminUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'state_access' => $request->state_access ?? [],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.admin-users.show', $adminUser)
            ->with('success', 'Admin user created successfully.');
    }

    public function show(AdminUser $adminUser)
    {
        $adminUser->load(['creator', 'createdAdmins']);

        $stats = [
            'created_admins' => $adminUser->createdAdmins->count(),
            'last_login' => $adminUser->last_login_at?->diffForHumans(),
            'account_age' => $adminUser->created_at->diffForHumans(),
        ];

        return view('admin.admin-users.show', compact('adminUser', 'stats'));
    }

    public function edit(AdminUser $adminUser)
    {
        $roles = [
            'super_admin' => 'Super Admin',
            'state_admin' => 'State Admin',
            'instructor' => 'Instructor',
        ];

        $states = ['florida', 'missouri', 'texas', 'delaware'];

        return view('admin.admin-users.edit', compact('adminUser', 'roles', 'states'));
    }

    public function update(Request $request, AdminUser $adminUser)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $adminUser->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:super_admin,state_admin,instructor',
            'permissions' => 'nullable|array',
            'state_access' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'state_access' => $request->state_access ?? [],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $adminUser->update($updateData);

        return redirect()->route('admin.admin-users.show', $adminUser)
            ->with('success', 'Admin user updated successfully.');
    }

    public function destroy(AdminUser $adminUser)
    {
        // Prevent deleting the last super admin
        if ($adminUser->isSuperAdmin() && AdminUser::where('role', 'super_admin')->count() <= 1) {
            return redirect()->route('admin.admin-users.index')
                ->with('error', 'Cannot delete the last super admin.');
        }

        // Prevent self-deletion
        if ($adminUser->id === Auth::guard('admin')->id()) {
            return redirect()->route('admin.admin-users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $adminUser->delete();

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user deleted successfully.');
    }

    public function toggleStatus(AdminUser $adminUser)
    {
        // Prevent deactivating the last super admin
        if ($adminUser->isSuperAdmin() && $adminUser->is_active && 
            AdminUser::where('role', 'super_admin')->where('is_active', true)->count() <= 1) {
            return redirect()->back()
                ->with('error', 'Cannot deactivate the last active super admin.');
        }

        // Prevent self-deactivation
        if ($adminUser->id === Auth::guard('admin')->id() && $adminUser->is_active) {
            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }

        $adminUser->update(['is_active' => !$adminUser->is_active]);

        $status = $adminUser->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Admin user {$status} successfully.");
    }
}