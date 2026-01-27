<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'permissions' => ['*']],
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => ['manage_courses', 'manage_users', 'view_reports']],
            ['name' => 'Instructor', 'slug' => 'instructor', 'permissions' => ['create_courses', 'manage_own_courses']],
            ['name' => 'Student', 'slug' => 'student', 'permissions' => ['enroll_courses', 'take_quizzes']],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        // Create super admin user
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role_id' => $superAdminRole->id,
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
    }
}
