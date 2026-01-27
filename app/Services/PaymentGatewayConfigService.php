<?php

namespace App\Services;

use App\Models\PaymentGateway;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Stripe\Stripe;

class PaymentGatewayConfigService
{
    public function getAllGateways(): Collection
    {
        return Cache::remember('payment_gateways', 3600, function () {
            return PaymentGateway::with('settings')->ordered()->get();
        });
    }

    public function getActiveGateways(): Collection
    {
        return $this->getAllGateways()->where('is_active', true);
    }

    public function getGateway(string $code): ?PaymentGateway
    {
        return $this->getAllGateways()->firstWhere('code', $code);
    }

    public function getGatewayConfig(string $code): array
    {
        $gateway = $this->getGateway($code);
        if (! $gateway) {
            return [];
        }

        $environment = $gateway->is_test_mode ? 'test' : 'production';
        $settings = $gateway->getAllSettings($environment);

        return [
            'gateway' => $gateway,
            'environment' => $environment,
            'settings' => $settings->pluck('setting_value', 'setting_key')->toArray(),
        ];
    }

    public function updateSettings(PaymentGateway $gateway, array $settings, string $environment): void
    {
        $oldSettings = $gateway->getAllSettings($environment)->pluck('setting_value', 'setting_key')->toArray();

        foreach ($settings as $key => $value) {
            $gateway->setSetting($key, $value, $environment, $this->isSensitiveKey($key));
        }

        $gateway->logAction('settings_changed', $oldSettings, $settings);
        $this->clearCache();
    }

    public function testConnection(PaymentGateway $gateway): array
    {
        $config = $this->getGatewayConfig($gateway->code);

        try {
            switch ($gateway->code) {
                case 'authorize_net':
                    return $this->testAuthorizeNetConnection($config['settings']);
                case 'stripe':
                    return $this->testStripeConnection($config['settings']);
                case 'paypal':
                    return $this->testPayPalConnection($config['settings']);
                default:
                    return ['success' => false, 'message' => 'Unknown gateway'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function testStripeConnection(array $settings): array
    {
        if (empty($settings['secret_key'])) {
            return ['success' => false, 'message' => 'Secret key not configured'];
        }

        Stripe::setApiKey($settings['secret_key']);

        try {
            \Stripe\Account::retrieve();

            return ['success' => true, 'message' => 'Stripe connection successful'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Stripe error: '.$e->getMessage()];
        }
    }

    protected function testPayPalConnection(array $settings): array
    {
        return ['success' => true, 'message' => 'PayPal connection test not implemented'];
    }

    public function activateGateway(PaymentGateway $gateway): array
    {
        $validation = $this->validateGatewaySettings($gateway);
        if (! $validation['valid']) {
            return [
                'success' => false,
                'message' => 'Missing required settings: '.implode(', ', $validation['missing']),
            ];
        }

        $test = $this->testConnection($gateway);
        if (! $test['success']) {
            return ['success' => false, 'message' => 'Connection test failed: '.$test['message']];
        }

        $gateway->update(['is_active' => true]);
        $gateway->logAction('activated');
        $this->clearCache();

        return ['success' => true, 'message' => 'Gateway activated successfully'];
    }

    public function deactivateGateway(PaymentGateway $gateway): void
    {
        $gateway->update(['is_active' => false]);
        $gateway->logAction('deactivated');
        $this->clearCache();
    }

    public function toggleMode(PaymentGateway $gateway): void
    {
        $oldMode = $gateway->is_test_mode ? 'test' : 'production';
        $gateway->update(['is_test_mode' => ! $gateway->is_test_mode]);
        $newMode = $gateway->is_test_mode ? 'test' : 'production';

        $gateway->logAction('mode_changed', ['mode' => $oldMode], ['mode' => $newMode]);
        $this->clearCache();
    }

    public function validateGatewaySettings(PaymentGateway $gateway): array
    {
        $required = $this->getRequiredSettings($gateway->code);
        $environment = $gateway->is_test_mode ? 'test' : 'production';
        $existing = $gateway->getAllSettings($environment)->pluck('setting_key')->toArray();
        $missing = array_diff($required, $existing);

        return [
            'valid' => empty($missing),
            'missing' => $missing,
        ];
    }

    protected function getRequiredSettings(string $gatewayCode): array
    {
        return match ($gatewayCode) {
            'authorize_net' => ['api_login_id', 'transaction_key'],
            'stripe' => ['publishable_key', 'secret_key'],
            'paypal' => ['client_id', 'client_secret'],
            default => [],
        };
    }

    protected function testAuthorizeNetConnection(array $settings): array
    {
        if (empty($settings['api_login_id']) || empty($settings['transaction_key'])) {
            return ['success' => false, 'message' => 'API Login ID or Transaction Key not configured'];
        }

        try {
            $merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType;
            $merchantAuthentication->setName($settings['api_login_id']);
            $merchantAuthentication->setTransactionKey($settings['transaction_key']);

            $request = new \net\authorize\api\contract\v1\AuthenticateTestRequest;
            $request->setMerchantAuthentication($merchantAuthentication);

            $controller = new \net\authorize\api\controller\AuthenticateTestController($request);

            $environment = $settings['environment'] ?? 'sandbox';
            if ($environment === 'production') {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
            } else {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
            }

            if ($response != null && $response->getMessages()->getResultCode() == 'Ok') {
                return ['success' => true, 'message' => 'Authorize.Net connection successful'];
            } else {
                $errorMessages = $response->getMessages()->getMessage();

                return ['success' => false, 'message' => 'Authorize.Net error: '.$errorMessages[0]->getText()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Authorize.Net error: '.$e->getMessage()];
        }
    }

    protected function isSensitiveKey(string $key): bool
    {
        $sensitiveKeys = [
            'secret_key', 'client_secret', 'transaction_key',
            'webhook_secret', 'api_key', 'private_key',
        ];

        return in_array($key, $sensitiveKeys) ||
               str_contains($key, 'secret') ||
               str_contains($key, 'password');
    }

    public function clearCache(): void
    {
        Cache::forget('payment_gateways');
    }

    public function getSettingsSchema(string $gatewayCode): array
    {
        return match ($gatewayCode) {
            'authorize_net' => [
                ['key' => 'api_login_id', 'label' => 'API Login ID', 'type' => 'text', 'sensitive' => false, 'required' => true],
                ['key' => 'transaction_key', 'label' => 'Transaction Key', 'type' => 'password', 'sensitive' => true, 'required' => true],
                ['key' => 'environment', 'label' => 'Environment', 'type' => 'select', 'options' => ['sandbox' => 'Sandbox (Test)', 'production' => 'Production'], 'sensitive' => false, 'required' => true],
            ],
            'stripe' => [
                ['key' => 'publishable_key', 'label' => 'Publishable Key', 'type' => 'text', 'sensitive' => false, 'required' => true],
                ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'sensitive' => true, 'required' => true],
                ['key' => 'webhook_secret', 'label' => 'Webhook Secret', 'type' => 'password', 'sensitive' => true, 'required' => false],
            ],
            'paypal' => [
                ['key' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'sensitive' => false, 'required' => true],
                ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'sensitive' => true, 'required' => true],
                ['key' => 'webhook_id', 'label' => 'Webhook ID', 'type' => 'text', 'sensitive' => false, 'required' => false],
            ],
            default => [],
        };
    }

    /**
     * Get Authorize.Net credentials with fallback to .env
     */
    public function getAuthorizeNetConfig(): array
    {
        $gateway = $this->getGateway('authorize_net');

        // If gateway exists and is active, try to use database settings
        if ($gateway && $gateway->is_active) {
            $environment = $gateway->is_test_mode ? 'test' : 'production';
            $settings = $gateway->getAllSettings($environment);

            $apiLoginId = $settings->firstWhere('setting_key', 'api_login_id')?->setting_value;
            $transactionKey = $settings->firstWhere('setting_key', 'transaction_key')?->setting_value;
            $env = $settings->firstWhere('setting_key', 'environment')?->setting_value;

            // Only use database if both required fields are present
            if ($apiLoginId && $transactionKey) {
                return [
                    'api_login_id' => $apiLoginId,
                    'transaction_key' => $transactionKey,
                    'environment' => $env ?? ($gateway->is_test_mode ? 'sandbox' : 'production'),
                    'source' => 'database',
                ];
            }
        }

        // Fallback to .env (if gateway is inactive or settings are missing)
        return [
            'api_login_id' => env('AUTHORIZENET_LOGIN_ID') ?? env('AUTHORIZENET_API_LOGIN_ID'),
            'transaction_key' => env('AUTHORIZENET_TRANSACTION_KEY'),
            'environment' => env('AUTHORIZENET_MODE') ?? env('AUTHORIZENET_ENVIRONMENT', 'sandbox'),
            'source' => 'env',
        ];
    }
}
