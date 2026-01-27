<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run()
    {
        // Get the first admin user or create a default one
        $adminUser = \App\Models\User::where('email', 'admin@example.com')->first();
        if (! $adminUser) {
            $adminUser = \App\Models\User::first(); // Use first available user
        }

        if (! $adminUser) {
            // Create a default admin user if none exists
            $adminUser = \App\Models\User::create([
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $templates = [
            [
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to {{site_name}}!',
                'content' => '<h1>Welcome {{user_name}}!</h1><p>Thank you for joining our traffic school platform.</p>',
                'variables' => ['user_name', 'site_name'],
                'category' => 'user',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Course Enrollment',
                'slug' => 'enrollment_confirmation',
                'subject' => 'Course Enrollment Confirmed',
                'content' => '<h2>Enrollment Confirmed</h2><p>Dear {{user_name}}, you have enrolled in {{course_name}}.</p>',
                'variables' => ['user_name', 'course_name'],
                'category' => 'user',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Payment Receipt',
                'slug' => 'payment_receipt',
                'subject' => 'Payment Receipt',
                'content' => '<h2>Payment Receipt</h2><p>Thank you for your payment of ${{amount}}.</p>',
                'variables' => ['amount', 'transaction_id'],
                'category' => 'user',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }
}
