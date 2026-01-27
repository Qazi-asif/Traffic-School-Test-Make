<?php

namespace Database\Seeders;

use App\Models\FloridaComplianceCheck;
use App\Models\FloridaLoginAttempt;
use App\Models\FloridaSecurityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class FloridaSecuritySeeder extends Seeder
{
    public function run(): void
    {
        // Create sample security logs
        $users = User::limit(5)->get();

        foreach ($users as $user) {
            FloridaSecurityLog::create([
                'user_id' => $user->id,
                'event_type' => 'login',
                'ip_address' => '192.168.1.'.rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'description' => 'User logged in successfully',
                'risk_level' => 'low',
                'created_at' => now()->subHours(rand(1, 24)),
            ]);
        }

        // Create failed login attempts
        FloridaLoginAttempt::create([
            'email' => 'test@example.com',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'successful' => false,
            'florida_compliance_check' => true,
            'attempted_at' => now()->subHours(2),
        ]);

        // Create compliance checks
        $checkTypes = ['daily', 'weekly', 'monthly'];
        $checkNames = [
            'Certificate inventory audit',
            'DICDS submission verification',
            'Security log review',
            'Payment gateway connectivity',
            'User account activity audit',
        ];

        foreach ($checkTypes as $type) {
            foreach ($checkNames as $name) {
                FloridaComplianceCheck::create([
                    'check_type' => $type,
                    'check_name' => $name,
                    'status' => collect(['passed', 'failed', 'warning'])->random(),
                    'details' => ['message' => 'Check completed', 'items_checked' => rand(10, 100)],
                    'performed_by' => $users->random()->id,
                    'performed_at' => now()->subDays(rand(1, 30)),
                    'next_due_date' => match ($type) {
                        'daily' => now()->addDay(),
                        'weekly' => now()->addWeek(),
                        'monthly' => now()->addMonth(),
                        default => now()->addDay()
                    },
                ]);
            }
        }
    }
}
