<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DelawareMasterSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🏛️ Starting Delaware Course Seeding...');
        $this->command->info('📚 Creating 3 Delaware Traffic School Courses');
        $this->command->info('');

        // Run all Delaware course seeders
        $this->call([
            Delaware6HourDefensiveDrivingSeeder::class,
            Delaware3HourRefresherSeeder::class,
            DelawareAggressiveDrivingSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Delaware course seeding completed successfully!');
        $this->command->info('');
        $this->command->info('Delaware Courses Created:');
        $this->command->info('1. 🛡️  Delaware 6-Hour Defensive Driving Course (10% Insurance Discount)');
        $this->command->info('   - Duration: 6 hours (360 minutes)');
        $this->command->info('   - Price: $39.99');
        $this->command->info('   - Benefits: 10% insurance discount + 3-point credit');
        $this->command->info('');
        $this->command->info('2. 🔄 Delaware 3-Hour Refresher Course (15% Insurance Discount)');
        $this->command->info('   - Duration: 3 hours (180 minutes)');
        $this->command->info('   - Price: $24.99');
        $this->command->info('   - Benefits: Increases discount to 15%');
        $this->command->info('');
        $this->command->info('3. 😤 Delaware Aggressive Driving Course');
        $this->command->info('   - Duration: 4 hours (240 minutes)');
        $this->command->info('   - Price: $49.99');
        $this->command->info('   - Purpose: Court-ordered for aggressive driving violations');
        $this->command->info('');
    }
}
