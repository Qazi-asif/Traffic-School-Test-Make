<?php
/**
 * Setup Test Users for Multi-State System
 * Creates test users for Florida, Missouri, Texas, Delaware, and Admin
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🚀 Setting up test users for multi-state system...\n\n";

$testUsers = [
    [
        'name' => 'Florida Test Student',
        'email' => 'florida@test.com',
        'password' => Hash::make('password123'),
        'state' => 'florida',
        'role' => 'student'
    ],
    [
        'name' => 'Missouri Test Student', 
        'email' => 'missouri@test.com',
        'password' => Hash::make('password123'),
        'state' => 'missouri',
        'role' => 'student'
    ],
    [
        'name' => 'Texas Test Student',
        'email' => 'texas@test.com', 
        'password' => Hash::make('password123'),
        'state' => 'texas',
        'role' => 'student'
    ],
    [
        'name' => 'Delaware Test Student',
        'email' => 'delaware@test.com',
        'password' => Hash::make('password123'), 
        'state' => 'delaware',
        'role' => 'student'
    ],
    [
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => Hash::make('admin123'),
        'state' => 'admin',
        'role' => 'admin'
    ]
];

foreach ($testUsers as $userData) {
    try {
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            $userData
        );
        
        echo "✅ Created/Updated: {$userData['name']} ({$userData['email']})\n";
        echo "   State: {$userData['state']} | Role: {$userData['role']}\n";
        echo "   Password: " . ($userData['role'] === 'admin' ? 'admin123' : 'password123') . "\n\n";
        
    } catch (Exception $e) {
        echo "❌ Error creating {$userData['name']}: " . $e->getMessage() . "\n\n";
    }
}

echo "🎯 Test Users Created!\n";
echo "Now you can:\n";
echo "1. Visit http://nelly-elearning.test/florida-simple\n";
echo "2. Visit http://nelly-elearning.test/missouri-simple\n"; 
echo "3. Visit http://nelly-elearning.test/texas-simple\n";
echo "4. Visit http://nelly-elearning.test/delaware-simple\n";
echo "5. Visit http://nelly-elearning.test/admin-simple\n";
echo "6. Test login with the credentials above\n";