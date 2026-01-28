<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use App\Models\Course;
use App\Models\FloridaCourse;
use Illuminate\Console\Command;

class TestPaymentGateways extends Command
{
    protected $signature = 'payment:test-gateways {--create-test-payment : Create a test payment}';
    protected $description = 'Test payment gateway configurations and create test payments';

    public function handle()
    {
        $this->info('💳 Testing Payment Gateway Configurations...');
        
        // Step 1: Check configuration
        $this->info("\nStep 1: Checking payment gateway configuration...");
        $this->checkConfiguration();
        
        // Step 2: Test API connections
        $this->info("\nStep 2: Testing API connections...");
        $this->testStripe();
        $this->testPayPal();
        $this->testAuthorizeNet();
        
        // Step 3: Analyze existing payments
        $this->info("\nStep 3: Analyzing existing payments...");
        $this->analyzePayments();
        
        // Step 4: Create test payment if requested
        if ($this->option('create-test-payment')) {
            $this->info("\nStep 4: Creating test payment...");
            $this->createTestPayment();
        }
        
        return 0;
    }
    
    private function checkConfiguration()
    {
        $stripeKey = config('services.stripe.key');
        $stripeSecret = config('services.stripe.secret');
        $paypalClientId = config('services.paypal.client_id');
        $paypalSecret = config('services.paypal.client_secret');
        $paypalMode = config('services.paypal.mode');
        $authorizeNetLoginId = config('services.authorizenet.login_id');
        $authorizeNetTransactionKey = config('services.authorizenet.transaction_key');
        $authorizeNetMode = config('services.authorizenet.mode');
        
        $this->table(['Gateway', 'Status', 'Environment', 'Notes'], [
            [
                'Stripe',
                $stripeSecret ? '✅ Configured' : '❌ Not configured',
                $stripeKey && strpos($stripeKey, 'pk_test_') === 0 ? 'TEST' : 'LIVE/Unknown',
                $stripeSecret ? 'Ready for testing' : 'Missing secret key'
            ],
            [
                'PayPal',
                $paypalClientId && $paypalSecret ? '✅ Configured' : '❌ Not configured',
                $paypalMode ?? 'Unknown',
                $paypalMode === 'sandbox' ? 'Ready for testing' : 'Check mode setting'
            ],
            [
                'Authorize.Net',
                $authorizeNetLoginId && $authorizeNetTransactionKey ? '✅ Configured' : '❌ Not configured',
                $authorizeNetMode ?? 'Unknown',
                'Check credentials validity'
            ]
        ]);
    }
    
    private function testStripe()
    {
        $this->line("💳 Testing Stripe...");
        
        $stripeSecret = config('services.stripe.secret');
        
        if (!$stripeSecret) {
            $this->warn("  ⚠️  Stripe not configured");
            return;
        }
        
        if (strpos($stripeSecret, 'sk_test_') !== 0) {
            $this->warn("  ⚠️  Not using test key - switch to test mode for safety");
            return;
        }
        
        try {
            \Stripe\Stripe::setApiKey($stripeSecret);
            
            // Test API connection
            $account = \Stripe\Account::retrieve();
            $this->info("  ✅ API connection successful");
            $this->line("    Account ID: {$account->id}");
            $this->line("    Country: {$account->country}");
            
            // Test creating a payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => 2999, // $29.99
                'currency' => 'usd',
                'description' => 'Test payment for traffic school course',
                'metadata' => [
                    'test' => 'true',
                    'source' => 'payment_gateway_test'
                ]
            ]);
            
            $this->info("  ✅ Test payment intent created: {$paymentIntent->id}");
            
            // Cancel the test payment intent
            $paymentIntent->cancel();
            $this->info("  ✅ Test payment intent cancelled");
            
        } catch (\Exception $e) {
            $this->error("  ❌ Stripe test failed: " . $e->getMessage());
        }
    }
    
    private function testPayPal()
    {
        $this->line("🅿️  Testing PayPal...");
        
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.client_secret');
        $mode = config('services.paypal.mode');
        
        if (!$clientId || !$secret) {
            $this->warn("  ⚠️  PayPal not configured");
            return;
        }
        
        $baseUrl = $mode === 'sandbox' ? 'https://api.sandbox.paypal.com' : 'https://api.paypal.com';
        
        try {
            // Test OAuth token
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $baseUrl . '/v1/oauth2/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_USERPWD, $clientId . ':' . $secret);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Accept-Language: en_US'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $tokenData = json_decode($response, true);
                $this->info("  ✅ OAuth token obtained successfully");
                $this->line("    Token type: " . $tokenData['token_type']);
                $this->line("    Expires in: " . $tokenData['expires_in'] . " seconds");
                $this->line("    Environment: " . ($mode === 'sandbox' ? 'SANDBOX' : 'LIVE'));
            } else {
                $this->error("  ❌ OAuth failed (HTTP {$httpCode})");
                $this->line("    Response: " . $response);
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ PayPal test failed: " . $e->getMessage());
        }
    }
    
    private function testAuthorizeNet()
    {
        $this->line("🏦 Testing Authorize.Net...");
        
        $loginId = config('services.authorizenet.login_id');
        $transactionKey = config('services.authorizenet.transaction_key');
        $mode = config('services.authorizenet.mode');
        
        if (!$loginId || !$transactionKey) {
            $this->warn("  ⚠️  Authorize.Net not configured");
            return;
        }
        
        $endpoint = $mode === 'production' 
            ? 'https://api.authorize.net/xml/v1/request.api'
            : 'https://apitest.authorize.net/xml/v1/request.api';
        
        try {
            // Test merchant authentication
            $xml = '<?xml version="1.0" encoding="utf-8"?>
            <getMerchantDetailsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name>' . $loginId . '</name>
                    <transactionKey>' . $transactionKey . '</transactionKey>
                </merchantAuthentication>
            </getMerchantDetailsRequest>';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: text/xml; charset=utf-8',
                'Content-Length: ' . strlen($xml)
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                if (strpos($response, 'Ok') !== false) {
                    $this->info("  ✅ Authentication successful");
                } else {
                    $this->warn("  ⚠️  Authentication response unclear");
                }
                $this->line("    Environment: " . ($mode === 'production' ? 'PRODUCTION' : 'SANDBOX'));
            } else {
                $this->error("  ❌ API test failed (HTTP {$httpCode})");
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ Authorize.Net test failed: " . $e->getMessage());
        }
    }
    
    private function analyzePayments()
    {
        $payments = Payment::all();
        $paymentsByMethod = $payments->groupBy('payment_method');
        
        $this->table(['Gateway', 'Total', 'Completed', 'Pending', 'Failed'], 
            $paymentsByMethod->map(function ($gatewayPayments, $gateway) {
                return [
                    $gateway,
                    $gatewayPayments->count(),
                    $gatewayPayments->where('status', 'completed')->count(),
                    $gatewayPayments->where('status', 'pending')->count(),
                    $gatewayPayments->where('status', 'failed')->count(),
                ];
            })->toArray()
        );
        
        $totalRevenue = $payments->where('status', 'completed')->sum('amount');
        $this->info("💰 Total revenue processed: $" . number_format($totalRevenue, 2));
        
        if ($payments->where('payment_method', 'dummy')->count() === $payments->count()) {
            $this->warn("⚠️  All payments are using dummy gateway - no real revenue processing");
        }
    }
    
    private function createTestPayment()
    {
        $user = User::first();
        $course = Course::first() ?? FloridaCourse::first();
        
        if (!$user || !$course) {
            $this->error("❌ No test user or course available");
            return;
        }
        
        try {
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $course->price ?? 29.99,
                'currency' => 'USD',
                'payment_method' => 'test',
                'status' => 'pending',
                'transaction_id' => 'TEST_' . time(),
                'description' => 'Test payment for ' . $course->title,
                'metadata' => json_encode([
                    'course_id' => $course->id,
                    'test' => true,
                    'created_by' => 'payment_gateway_test_command'
                ])
            ]);
            
            $this->info("✅ Test payment created: {$payment->id}");
            $this->line("   User: {$user->email}");
            $this->line("   Course: {$course->title}");
            $this->line("   Amount: $" . number_format($payment->amount, 2));
            
            // Simulate payment completion
            $payment->update(['status' => 'completed']);
            $this->info("✅ Test payment marked as completed");
            
        } catch (\Exception $e) {
            $this->error("❌ Test payment creation failed: " . $e->getMessage());
        }
    }
}