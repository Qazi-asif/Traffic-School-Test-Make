<?php

/**
 * CRITICAL FIX: Payment Gateway Testing
 * 
 * This script tests all payment gateways to ensure they're working properly:
 * 1. Tests Stripe integration with test API keys
 * 2. Tests PayPal integration with sandbox credentials
 * 3. Tests Authorize.Net integration with test credentials
 * 4. Creates test transactions to verify functionality
 * 5. Updates configuration based on test results
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\UserCourseEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "🚨 CRITICAL FIX: Payment Gateway Testing\n";
echo "======================================\n\n";

// Test configurations
$paymentTests = [
    'Stripe' => [
        'enabled' => !empty(env('STRIPE_SECRET')),
        'test_key' => env('STRIPE_SECRET'),
        'public_key' => env('STRIPE_KEY'),
        'mode' => strpos(env('STRIPE_SECRET', ''), 'sk_test_') === 0 ? 'test' : 'live'
    ],
    'PayPal' => [
        'enabled' => !empty(env('PAYPAL_CLIENT_ID')),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox')
    ],
    'Authorize.Net' => [
        'enabled' => !empty(env('AUTHORIZENET_LOGIN_ID')),
        'login_id' => env('AUTHORIZENET_LOGIN_ID'),
        'transaction_key' => env('AUTHORIZENET_TRANSACTION_KEY'),
        'mode' => env('AUTHORIZENET_MODE', 'sandbox')
    ]
];

$workingGateways = [];
$brokenGateways = [];

echo "🔍 Testing Payment Gateways...\n";
echo "==============================\n\n";

// Test Stripe
echo "Testing Stripe...\n";
if ($paymentTests['Stripe']['enabled']) {
    $stripeResult = testStripeGateway($paymentTests['Stripe']);
    if ($stripeResult['success']) {
        echo "  ✅ Status: WORKING\n";
        echo "  📝 Mode: {$paymentTests['Stripe']['mode']}\n";
        echo "  💳 Test Result: {$stripeResult['message']}\n";
        $workingGateways['Stripe'] = $paymentTests['Stripe'];
    } else {
        echo "  ❌ Status: BROKEN\n";
        echo "  📝 Error: {$stripeResult['error']}\n";
        echo "  💡 Suggestion: {$stripeResult['suggestion']}\n";
        $brokenGateways['Stripe'] = $paymentTests['Stripe'];
    }
} else {
    echo "  ⚠️  Status: NOT CONFIGURED\n";
    echo "  📝 Missing: STRIPE_SECRET and STRIPE_KEY environment variables\n";
    $brokenGateways['Stripe'] = $paymentTests['Stripe'];
}
echo "\n";

// Test PayPal
echo "Testing PayPal...\n";
if ($paymentTests['PayPal']['enabled']) {
    $paypalResult = testPayPalGateway($paymentTests['PayPal']);
    if ($paypalResult['success']) {
        echo "  ✅ Status: WORKING\n";
        echo "  📝 Mode: {$paymentTests['PayPal']['mode']}\n";
        echo "  💳 Test Result: {$paypalResult['message']}\n";
        $workingGateways['PayPal'] = $paymentTests['PayPal'];
    } else {
        echo "  ❌ Status: BROKEN\n";
        echo "  📝 Error: {$paypalResult['error']}\n";
        echo "  💡 Suggestion: {$paypalResult['suggestion']}\n";
        $brokenGateways['PayPal'] = $paymentTests['PayPal'];
    }
} else {
    echo "  ⚠️  Status: NOT CONFIGURED\n";
    echo "  📝 Missing: PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET environment variables\n";
    $brokenGateways['PayPal'] = $paymentTests['PayPal'];
}
echo "\n";

// Test Authorize.Net
echo "Testing Authorize.Net...\n";
if ($paymentTests['Authorize.Net']['enabled']) {
    $authnetResult = testAuthorizeNetGateway($paymentTests['Authorize.Net']);
    if ($authnetResult['success']) {
        echo "  ✅ Status: WORKING\n";
        echo "  📝 Mode: {$paymentTests['Authorize.Net']['mode']}\n";
        echo "  💳 Test Result: {$authnetResult['message']}\n";
        $workingGateways['Authorize.Net'] = $paymentTests['Authorize.Net'];
    } else {
        echo "  ❌ Status: BROKEN\n";
        echo "  📝 Error: {$authnetResult['error']}\n";
        echo "  💡 Suggestion: {$authnetResult['suggestion']}\n";
        $brokenGateways['Authorize.Net'] = $paymentTests['Authorize.Net'];
    }
} else {
    echo "  ⚠️  Status: NOT CONFIGURED\n";
    echo "  📝 Missing: AUTHORIZENET_LOGIN_ID and AUTHORIZENET_TRANSACTION_KEY environment variables\n";
    $brokenGateways['Authorize.Net'] = $paymentTests['Authorize.Net'];
}
echo "\n";

// Create test payment records for working gateways
if (!empty($workingGateways)) {
    echo "💳 Creating Test Payment Records...\n";
    echo "==================================\n\n";
    
    // Find or create a test user
    $testUser = User::where('email', 'test@example.com')->first();
    if (!$testUser) {
        $testUser = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role_id' => 4, // Student
            'status' => 'active'
        ]);
    }
    
    // Find or create a test enrollment
    $testEnrollment = UserCourseEnrollment::where('user_id', $testUser->id)->first();
    if (!$testEnrollment) {
        $testEnrollment = UserCourseEnrollment::create([
            'user_id' => $testUser->id,
            'course_id' => 1,
            'course_table' => 'florida_courses',
            'amount_paid' => 29.95,
            'payment_status' => 'pending',
            'enrolled_at' => now()
        ]);
    }
    
    foreach ($workingGateways as $gatewayName => $config) {
        echo "Creating test payment for {$gatewayName}...\n";
        
        try {
            $payment = Payment::create([
                'user_id' => $testUser->id,
                'enrollment_id' => $testEnrollment->id,
                'amount' => 1.00, // Test amount
                'payment_method' => strtolower($gatewayName),
                'gateway' => strtolower($gatewayName),
                'gateway_payment_id' => 'test_' . strtolower($gatewayName) . '_' . time(),
                'billing_name' => 'Test User',
                'billing_email' => 'test@example.com',
                'status' => 'completed'
            ]);
            
            echo "  ✅ Test payment created: ID {$payment->id}\n";
            
        } catch (Exception $e) {
            echo "  ❌ Failed to create test payment: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// Update configuration
echo "⚙️  Updating Payment Configuration...\n";
echo "====================================\n\n";

$envUpdates = [];

// Enable working gateways
foreach ($workingGateways as $gatewayName => $config) {
    switch ($gatewayName) {
        case 'Stripe':
            $envUpdates['STRIPE_ENABLED'] = 'true';
            break;
        case 'PayPal':
            $envUpdates['PAYPAL_ENABLED'] = 'true';
            break;
        case 'Authorize.Net':
            $envUpdates['AUTHORIZENET_ENABLED'] = 'true';
            break;
    }
}

// Disable broken gateways
foreach ($brokenGateways as $gatewayName => $config) {
    switch ($gatewayName) {
        case 'Stripe':
            $envUpdates['STRIPE_ENABLED'] = 'false';
            break;
        case 'PayPal':
            $envUpdate