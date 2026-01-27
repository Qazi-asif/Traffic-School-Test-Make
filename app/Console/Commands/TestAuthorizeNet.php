<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestAuthorizeNet extends Command
{
    protected $signature = 'authorizenet:test';

    protected $description = 'Test Authorize.Net configuration';

    public function handle()
    {
        $this->info('Testing Authorize.Net Configuration...');
        $this->newLine();

        $loginId = config('payment.authorizenet.login_id');
        $transactionKey = config('payment.authorizenet.transaction_key');
        $mode = config('payment.authorizenet.mode');

        $this->info('Login ID: '.($loginId ?: 'NOT SET'));
        $this->info('Transaction Key: '.($transactionKey ? str_repeat('*', strlen($transactionKey) - 4).substr($transactionKey, -4) : 'NOT SET'));
        $this->info('Mode: '.$mode);
        $this->newLine();

        if (! $loginId || ! $transactionKey) {
            $this->error('❌ Credentials are missing!');
            $this->warn('Make sure your .env file has:');
            $this->line('AUTHORIZENET_LOGIN_ID=your_login_id');
            $this->line('AUTHORIZENET_TRANSACTION_KEY=your_transaction_key');
            $this->line('AUTHORIZENET_MODE=sandbox');
            $this->newLine();
            $this->warn('After updating .env, run: php artisan config:clear');

            return 1;
        }

        $this->info('✅ Configuration looks good!');
        $this->newLine();
        $this->info('Test card for sandbox:');
        $this->line('Card: 4007000000027');
        $this->line('Expiry: 12/2025');
        $this->line('CVV: 123');

        return 0;
    }
}
