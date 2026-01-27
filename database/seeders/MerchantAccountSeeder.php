<?php

namespace Database\Seeders;

use App\Models\MerchantAccount;
use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class MerchantAccountSeeder extends Seeder
{
    public function run(): void
    {
        $gateway = PaymentGateway::where('code', 'authorize_net')->first();

        if (! $gateway) {
            $this->command->warn('Authorize.Net gateway not found. Run PaymentGatewaySeeder first.');

            return;
        }

        MerchantAccount::updateOrCreate(
            ['account_identifier' => env('AUTHORIZENET_LOGIN_ID', '3QsuX59B')],
            [
                'gateway_id' => $gateway->id,
                'account_name' => 'Primary Authorize.Net Account',
                'account_identifier' => env('AUTHORIZENET_LOGIN_ID', '3QsuX59B'),
                'account_email' => env('MERCHANT_EMAIL', 'merchant@example.com'),
                'is_primary' => true,
                'is_active' => true,
                'currency' => 'USD',
                'payout_schedule' => 'monthly',
                'payout_day' => 1,
                'minimum_payout' => 100.00,
                'reserve_percent' => 0,
            ]
        );

        $this->command->info('✓ Merchant account created for Authorize.Net');
    }
}
