<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TexasMasterSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting Texas course seeding...');

        // Run all Texas-related seeders
        $this->call([
            TexasDefensiveDrivingCompleteSeeder::class,
            TexasFaqSeeder::class,
        ]);

        $this->command->info('Texas course seeding completed successfully!');
        $this->command->info('');
        $this->command->info('Texas Course Details:');
        $this->command->info('- Provider License: CP007');
        $this->command->info('- Course Type: Defensive Driving/Ticket Dismissal');
        $this->command->info('- Duration: 6 hours (360 minutes)');
        $this->command->info('- Minimum Pass Score: 70%');
        $this->command->info('- Student Data Retention: 3 years');
        $this->command->info('- Certificate Numbers: Range-based (e.g., 8567-8600)');
        $this->command->info('- DMV Transmission: Required');
        $this->command->info('');
        $this->command->info('Course Options Available:');
        $this->command->info('1. Defensive Driving/Ticket Dismissal');
        $this->command->info('2. Insurance Discount');
        $this->command->info('');
        $this->command->info('Additional Components Seeded:');
        $this->command->info('- Complete course content (7 chapters + final exam)');
        $this->command->info('- Chapter quiz questions');
        $this->command->info('- Final exam questions (25 questions)');
        $this->command->info('- Comprehensive FAQ section');
    }
}
