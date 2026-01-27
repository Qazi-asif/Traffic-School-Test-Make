<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixUserRoles extends Command
{
    protected $signature = 'fix:user-roles';
    protected $description = 'Fix user roles and ensure proper database setup';

    public function handle()
    {
        $this->info('🔧 Fixing user roles and database setup...');

        // Step 1: Ensure roles exist
        $this->info('1. Creating/verifying roles...');
        
        $roles = [
            ['id' => 1, 'name' => 'Super Admin', 'slug' => 'super-admin', 'permissions' => ['*']],
            ['id' => 2, 'name' => 'Admin', 'slug' => 'admin', 'permissions' => ['manage_courses', 'manage_users', 'view_reports']],
            ['id' => 3, 'name' => 'Instructor', 'slug' => 'instructor', 'permissions' => ['create_courses', 'manage_own_courses']],
            ['id' => 4, 'name' => 'Student', 'slug' => 'student', 'permissions' => ['enroll_courses', 'take_quizzes']],
        ];

        foreach ($roles as $roleData) {
            $role = Role::find($roleData['id']);
            if (!$role) {
                Role::create([
                    'id' => $roleData['id'],
                    'name' => $roleData['name'],
                    'slug' => $roleData['slug'],
                    'permissions' => json_encode($roleData['permissions'])
                ]);
                $this->info("  ✅ Created role: {$roleData['name']}");
            } else {
                $this->info("  ✓ Role exists: {$role->name}");
            }
        }

        // Step 2: Fix users without proper role_id
        $this->info('2. Fixing users without proper roles...');
        
        $usersWithoutRole = User::whereNull('role_id')->orWhere('role_id', 0)->get();
        
        if ($usersWithoutRole->count() > 0) {
            foreach ($usersWithoutRole as $user) {
                $user->update(['role_id' => 4]); // Student role
                $this->info("  ✅ Fixed user: {$user->first_name} {$user->last_name} ({$user->email})");
            }
        } else {
            $this->info('  ✓ All users have proper roles');
        }

        // Step 3: Create admin user if none exists
        $this->info('3. Ensuring admin user exists...');
        
        $adminExists = User::whereHas('role', function($query) {
            $query->whereIn('slug', ['super-admin', 'admin']);
        })->exists();

        if (!$adminExists) {
            $adminUser = User::create([
                'role_id' => 1, // Super Admin
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'status' => 'active',
            ]);
            $this->info("  ✅ Created admin user: admin@example.com (password: password123)");
        } else {
            $this->info('  ✓ Admin user already exists');
        }

        // Step 4: Show summary
        $this->info('4. Summary:');
        $totalUsers = User::count();
        $usersWithRoles = User::whereNotNull('role_id')->where('role_id', '>', 0)->count();
        $adminUsers = User::whereHas('role', function($query) {
            $query->whereIn('slug', ['super-admin', 'admin']);
        })->count();
        $studentUsers = User::where('role_id', 4)->count();

        $this->info("  📊 Total users: {$totalUsers}");
        $this->info("  📊 Users with roles: {$usersWithRoles}");
        $this->info("  👑 Admin users: {$adminUsers}");
        $this->info("  🎓 Student users: {$studentUsers}");

        if ($totalUsers === $usersWithRoles) {
            $this->info('  ✅ All users have proper roles!');
        } else {
            $this->error('  ❌ Some users still missing roles');
        }

        $this->info('🎉 Fix completed!');
        
        return 0;
    }
}