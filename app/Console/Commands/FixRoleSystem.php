<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRoleSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix role system to resolve 403 errors in admin modules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 FIXING ROLE SYSTEM - Resolving 403 Errors...');
        $this->info(str_repeat('=', 60));

        try {
            // Show current problematic state
            $this->info('1. 📊 CURRENT ROLES (PROBLEMATIC):');
            $this->info(str_repeat('-', 40));
            
            $currentRoles = DB::table('roles')->orderBy('id')->get();
            foreach ($currentRoles as $role) {
                $this->info("   ID: {$role->id}, Name: {$role->name}, Slug: {$role->slug}");
            }

            // Execute the fix
            $this->info('');
            $this->info('2. 🔧 EXECUTING FIX:');
            $this->info(str_repeat('-', 20));

            // Step 1: Set temporary slugs to avoid conflicts
            $this->info('Setting temporary slugs...');
            DB::table('roles')->where('id', 1)->update(['slug' => 'temp-super-admin']);
            DB::table('roles')->where('id', 2)->update(['slug' => 'temp-admin']);
            DB::table('roles')->where('id', 3)->update(['slug' => 'temp-user']);
            $this->info('✅ Temporary slugs set');

            // Step 2: Set correct roles
            $this->info('Setting correct roles...');
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
            $this->info('✅ Correct roles set');

            // Step 3: Ensure admin user exists
            $this->info('Ensuring admin user exists...');
            $adminCount = DB::table('users')->whereIn('role_id', [1, 2])->count();
            
            if ($adminCount == 0) {
                $this->info('No admin users found, promoting first user...');
                $firstUser = DB::table('users')->orderBy('id')->first();
                if ($firstUser) {
                    DB::table('users')->where('id', $firstUser->id)->update([
                        'role_id' => 1,
                        'updated_at' => now()
                    ]);
                    $this->info("✅ User {$firstUser->name} promoted to Super Admin");
                }
            } else {
                $this->info("✅ Admin users already exist ($adminCount found)");
            }

            // Step 4: Show results
            $this->info('');
            $this->info('3. ✅ FIXED ROLES:');
            $this->info(str_repeat('-', 20));
            
            $fixedRoles = DB::table('roles')->orderBy('id')->get();
            foreach ($fixedRoles as $role) {
                $this->info("   ID: {$role->id}, Name: {$role->name}, Slug: {$role->slug}");
            }

            // Step 5: Show admin users
            $this->info('');
            $this->info('4. 👥 ADMIN USERS:');
            $this->info(str_repeat('-', 15));
            
            $adminUsers = DB::table('users')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->whereIn('users.role_id', [1, 2])
                ->select('users.id', 'users.name', 'users.email', 'users.role_id', 'roles.name as role_name', 'roles.slug as role_slug')
                ->orderBy('users.id')
                ->get();
            
            foreach ($adminUsers as $user) {
                $this->info("   {$user->name} ({$user->email}): {$user->role_name} (ID: {$user->role_id})");
            }

            $this->info('');
            $this->info(str_repeat('=', 60));
            $this->info('🎉 ROLE FIX COMPLETED SUCCESSFULLY!');
            $this->info(str_repeat('=', 60));

            $this->info('');
            $this->info('✅ WHAT WAS FIXED:');
            $this->info('   • Role ID 1: Super Admin -> slug: \'super-admin\'');
            $this->info('   • Role ID 2: Admin -> slug: \'admin\'');
            $this->info('   • Role ID 3: User -> slug: \'user\'');
            $this->info('   • Ensured admin users exist');

            $this->info('');
            $this->info('🔗 ADMIN ROUTES SHOULD NOW WORK:');
            $this->info('   • http://nelly-elearning.test/admin/state-transmissions');
            $this->info('   • http://nelly-elearning.test/admin/certificates');
            $this->info('   • http://nelly-elearning.test/admin/users');
            $this->info('   • http://nelly-elearning.test/admin/dashboard');
            $this->info('   • http://nelly-elearning.test/booklets');

            $this->info('');
            $this->info('⚠️  NEXT STEPS:');
            $this->info('   • Clear browser cache and cookies');
            $this->info('   • Log out and log back in');
            $this->info('   • Test admin routes');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}