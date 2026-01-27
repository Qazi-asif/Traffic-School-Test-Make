<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetCaliforniaTvccPassword extends Command
{
    protected $signature = 'california:set-tvcc-password {password}';
    protected $description = 'Set the California TVCC password in the database';

    public function handle(): int
    {
        $password = $this->argument('password');

        try {
            // Create table if it doesn't exist
            if (!DB::getSchemaBuilder()->hasTable('tvcc_passwords')) {
                DB::statement('
                    CREATE TABLE tvcc_passwords (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        password VARCHAR(255) NOT NULL,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ');
                $this->info('Created tvcc_passwords table');
            }

            // Insert or update password
            DB::table('tvcc_passwords')->insert([
                'password' => $password,
                'updated_at' => now(),
            ]);

            $this->info('California TVCC password has been set successfully');
            $this->info('Password: ' . str_repeat('*', strlen($password)));

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to set TVCC password: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}