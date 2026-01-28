<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the role system to resolve 403 errors in admin modules.
     * The AdminMiddleware expects role_id 1 and 2 for admin access.
     * The RoleMiddleware expects specific role slugs.
     */
    public function up(): void
    {
        // First, set temporary slugs to avoid unique constraint conflicts
        DB::table('roles')->where('id', 1)->update(['slug' => 'temp-super-admin']);
        DB::table('roles')->where('id', 2)->update(['slug' => 'temp-admin']);
        DB::table('roles')->where('id', 3)->update(['slug' => 'temp-user']);

        // Now set the correct roles that match both middleware systems
        DB::table('roles')->where('id', 1)->update([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full system access',
            'updated_at' => now()
        ]);

        DB::table('roles')->where('id', 2)->update([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrative access',
            'updated_at' => now()
        ]);

        DB::table('roles')->where('id', 3)->update([
            'name' => 'User',
            'slug' => 'user',
            'description' => 'Regular user access',
            'updated_at' => now()
        ]);

        // Ensure we have at least one admin user
        $adminCount = DB::table('users')->whereIn('role_id', [1, 2])->count();
        
        if ($adminCount === 0) {
            // Promote the first user to Super Admin
            $firstUser = DB::table('users')->orderBy('id')->first();
            if ($firstUser) {
                DB::table('users')->where('id', $firstUser->id)->update([
                    'role_id' => 1,
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original problematic state (not recommended)
        DB::table('roles')->where('id', 1)->update([
            'name' => 'Student',
            'slug' => 'student',
            'description' => 'Student role',
            'updated_at' => now()
        ]);

        DB::table('roles')->where('id', 2)->update([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role',
            'updated_at' => now()
        ]);

        DB::table('roles')->where('id', 3)->update([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Super Admin role',
            'updated_at' => now()
        ]);
    }
};