<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FloridaRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'DRS Provider Admin',
                'slug' => 'drs_provider_admin',
                'permissions' => [
                    'order_certificates',
                    'manage_schools',
                    'manage_instructors',
                    'distribute_certificates',
                    'manage_users',
                ],
            ],
            [
                'name' => 'DRS Provider User',
                'slug' => 'drs_provider_user',
                'permissions' => [
                    'enter_school_data',
                    'enter_instructor_data',
                    'distribute_certificates',
                ],
            ],
            [
                'name' => 'DRS School Admin',
                'slug' => 'drs_school_admin',
                'permissions' => [
                    'order_certificates',
                    'enter_student_data',
                    'view_school_reports',
                ],
            ],
            [
                'name' => 'Student',
                'slug' => 'student',
                'permissions' => [
                    'take_courses',
                    'view_certificates',
                    'view_progress',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // Create super admin user
        $adminRole = Role::where('slug', 'drs_provider_admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@floridatraffic.com'],
            [
                'role_id' => $adminRole->id,
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password123'),
                'status' => 'active',
            ]
        );
    }
}
