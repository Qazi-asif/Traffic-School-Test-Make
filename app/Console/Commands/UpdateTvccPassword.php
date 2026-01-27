<?php

namespace App\Console\Commands;

use App\Models\TvccPassword;
use Illuminate\Console\Command;

class UpdateTvccPassword extends Command
{
    protected $signature = 'tvcc:password {password?}';
    protected $description = 'Update California TVCC password';

    public function handle(): int
    {
        $password = $this->argument('password');

        if (!$password) {
            $password = $this->secret('Enter TVCC password');
        }

        if (empty($password)) {
            $this->error('Password cannot be empty');
            return 1;
        }

        try {
            TvccPassword::updatePassword($password);
            $this->info('✓ TVCC password updated successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to update password: ' . $e->getMessage());
            return 1;
        }
    }
}
