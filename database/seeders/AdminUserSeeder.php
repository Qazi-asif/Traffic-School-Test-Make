<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create Super Admin
        AdminUser::create([
            'name' => 'Super Administrator',
            'email' => 'admin@dummiestrafficschool.com',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'permissions' => [
                'manage_users',
                'manage_courses',
                'manage_certificates',
                'manage_payments',
                'manage_settings',
                'view_reports',
                'manage_admin_users',
                'system_maintenance'
            ],
            'state_access' => ['florida', 'missouri', 'texas', 'delaware'],
            'is_active' => true,
        ]);

        // Create Florida State Admin
        AdminUser::create([
            'name' => 'Florida State Admin',
            'email' => 'florida@dummiestrafficschool.com',
            'password' => Hash::make('florida123'),
            'role' => 'state_admin',
            'permissions' => [
                'manage_users',
                'manage_courses',
                'manage_certificates',
                'manage_payments',
                'view_reports'
            ],
            'state_access' => ['florida'],
            'is_active' => true,
            'created_by' => 1,
        ]);

        // Create Missouri State Admin
        AdminUser::create([
            'name' => 'Missouri State Admin',
            'email' => 'missouri@dummiestrafficschool.com',
            'password' => Hash::make('missouri123'),
            'role' => 'state_admin',
            'permissions' => [
                'manage_users',
                'manage_courses',
                'manage_certificates',
                'manage_payments',
                'view_reports'
            ],
            'state_access' => ['missouri'],
            'is_active' => true,
            'created_by' => 1,
        ]);

        // Create Texas State Admin
        AdminUser::create([
            'name' => 'Texas State Admin',
            'email' => 'texas@dummiestrafficschool.com',
            'password' => Hash::make('texas123'),
            'role' => 'state_admin',
            'permissions' => [
                'manage_users',
                'manage_courses',
                'manage_certificates',
                'manage_payments',
                'view_reports'
            ],
            'state_access' => ['texas'],
            'is_active' => true,
            'created_by' => 1,
        ]);

        // Create Delaware State Admin
        AdminUser::create([
            'name' => 'Delaware State Admin',
            'email' => 'delaware@dummiestrafficschool.com',
            'password' => Hash::make('delaware123'),
            'role' => 'state_admin',
            'permissions' => [
                'manage_users',
                'manage_courses',
                'manage_certificates',
                'manage_payments',
                'view_reports'
            ],
            'state_access' => ['delaware'],
            'is_active' => true,
            'created_by' => 1,
        ]);

        // Create Sample Instructor
        AdminUser::create([
            'name' => 'Course Instructor',
            'email' => 'instructor@dummiestrafficschool.com',
            'password' => Hash::make('instructor123'),
            'role' => 'instructor',
            'permissions' => [
                'manage_courses',
                'view_reports'
            ],
            'state_access' => ['florida', 'missouri'],
            'is_active' => true,
            'created_by' => 1,
        ]);
    }
}