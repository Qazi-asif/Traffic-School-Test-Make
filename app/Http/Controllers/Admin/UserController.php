<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'enrollments'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('account_locked', false);
            } elseif ($request->status === 'locked') {
                $query->where('account_locked', true);
            }
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->paginate(20);
        $roles = Role::orderBy('name')->get();

        $stats = [
            'total' => User::count(),
            'active' => User::where('account_locked', false)->count(),
            'locked' => User::where('account_locked', true)->count(),
            'with_enrollments' => User::has('enrollments')->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'stats'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:10',
            'driver_license' => 'nullable|string|max:50',
            'license_state' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'driver_license' => $request->driver_license,
            'license_state' => $request->license_state,
            'account_locked' => false,
            'registration_completed_at' => now(),
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['role', 'enrollments.course', 'certificates', 'payments']);

        $stats = [
            'total_enrollments' => $user->enrollments->count(),
            'active_enrollments' => $user->enrollments->where('status', 'active')->count(),
            'completed_enrollments' => $user->enrollments->whereNotNull('completed_at')->count(),
            'total_payments' => $user->payments->sum('amount'),
            'total_certificates' => $user->certificates->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:10',
            'driver_license' => 'nullable|string|max:50',
            'license_state' => 'nullable|string|max:50',
            'account_locked' => 'boolean',
        ]);

        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'driver_license' => $request->driver_license,
            'license_state' => $request->license_state,
            'account_locked' => $request->boolean('account_locked'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Check if user has enrollments
        if ($user->enrollments()->count() > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user with existing enrollments.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'account_locked' => !$user->account_locked,
            'locked_at' => $user->account_locked ? null : now(),
        ]);

        $status = $user->account_locked ? 'locked' : 'unlocked';
        
        return redirect()->back()
            ->with('success', "User account {$status} successfully.");
    }

    public function export(Request $request)
    {
        $query = User::with(['role', 'enrollments']);

        // Apply same filters as index
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('account_locked', false);
            } elseif ($request->status === 'locked') {
                $query->where('account_locked', true);
            }
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->get();

        $filename = 'users_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Name',
                'Email',
                'Role',
                'Phone',
                'State',
                'Driver License',
                'Status',
                'Enrollments',
                'Registration Date'
            ]);

            // Data rows
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->first_name . ' ' . $user->last_name,
                    $user->email,
                    $user->role->name ?? 'N/A',
                    $user->phone,
                    $user->state,
                    $user->driver_license,
                    $user->account_locked ? 'Locked' : 'Active',
                    $user->enrollments->count(),
                    $user->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}