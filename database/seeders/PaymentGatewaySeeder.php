<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        // Create Authorize.Net gateway
        $gateway = PaymentGateway::updateOrCreate(
            ['code' => 'authorize_net'],
            [
                'name' => 'Authorize.Net',
                'code' => 'authorize_net',
                'display_name' => 'Credit/Debit Card',
                'description' => 'Pay securely with your credit or debit card via Authorize.Net',
                'icon' => null,
                'is_active' => true,
                'is_test_mode' => (env('AUTHORIZENET_MODE') ?? env('AUTHORIZENET_ENVIRONMENT', 'sandbox')) !== 'production',
                'display_order' => 1,
                'supported_currencies' => ['USD'],
                'transaction_fee_percent' => 2.9,
                'transaction_fee_fixed' => 0.30,
            ]
        );

        // Import credentials from .env if they exist
        $apiLoginId = env('AUTHORIZENET_LOGIN_ID') ?? env('AUTHORIZENET_API_LOGIN_ID');
        $transactionKey = env('AUTHORIZENET_TRANSACTION_KEY');
        $environment = env('AUTHORIZENET_MODE') ?? env('AUTHORIZENET_ENVIRONMENT', 'sandbox');

        if ($apiLoginId && $transactionKey) {
            // Determine which environment to use
            $envType = $environment === 'production' ? 'production' : 'test';

            // Set the credentials
            $gateway->setSetting('api_login_id', $apiLoginId, $envType, false);
            $gateway->setSetting('transaction_key', $transactionKey, $envType, true);
            $gateway->setSetting('environment', $environment, $envType, false);

            echo "✓ Imported Authorize.Net credentials from .env to database ({$envType} environment)\n";
        } else {
            echo "⚠ No Authorize.Net credentials found in .env file\n";
        }

        // Delete other gateways if they exist
        PaymentGateway::whereIn('code', ['stripe', 'paypal', 'dummy'])->delete();

        echo "✓ Removed Stripe, PayPal, and Dummy gateways\n";
    }
}
