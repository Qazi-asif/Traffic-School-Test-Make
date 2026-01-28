<?php
/**
 * Create Test Users for Login
 */

echo "👥 CREATING TEST USERS\n";
echo "======================\n\n";

try {
    // Bootstrap Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    echo "✅ Laravel bootstrapped successfully\n";
    
    // Create test users
    $users = [
        [
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'password123',
            'phone' => '555-0001',
            'state_code' => 'florida',
            'role' => 'student'
        ],
        [
            'name' => 'Admin User',
            'email' => 'admin@test.com', 
            'password' => 'admin123',
            'phone' => '555-0002',
            'state_code' => 'florida',
            'role' => 'admin'
        ],
        [
            'name' => 'Missouri Student',
            'email' => 'missouri@test.com',
            'password' => 'password123',
            'phone' => '555-0003',
            'state_code' => 'missouri',
            'role' => 'student'
        ],
        [
            'name' => 'Texas Student',
            'email' => 'texas@test.com',
            'password' => 'password123',
            'phone' => '555-0004',
            'state_code' => 'texas',
            'role' => 'student'
        ]
    ];
    
    foreach ($users as $userData) {
        try {
            // Check if user already exists
            $existingUser = DB::table('users')->where('email', $userData['email'])->first();
            
            if ($existingUser) {
                echo "⚪ User already exists: {$userData['email']}\n";
                continue;
            }
            
            // Create new user
            $userId = DB::table('users')->insertGetId([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'phone' => $userData['phone'],
                'state_code' => $userData['state_code'],
                'role' => $userData['role'],
                'is_active' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "✅ Created user: {$userData['name']} ({$userData['email']}) - ID: {$userId}\n";
            
        } catch (Exception $e) {
            echo "❌ Failed to create {$userData['email']}: " . $e->getMessage() . "\n";
        }
    }
    
    // Create some sample courses
    echo "\n📚 CREATING SAMPLE COURSES:\n";
    echo "============================\n";
    
    $courses = [
        [
            'table' => 'florida_courses',
            'title' => 'Florida Defensive Driving Course',
            'description' => 'FLHSMV approved 8-hour defensive driving course',
            'duration_hours' => 8,
            'passing_score' => 80,
            'price' => 25.00
        ],
        [
            'table' => 'missouri_courses', 
            'title' => 'Missouri Driver Improvement Program',
            'description' => 'Missouri DOR approved driver improvement course',
            'duration_hours' => 8,
            'passing_score' => 70,
            'price' => 30.00
        ]
    ];
    
    foreach ($courses as $courseData) {
        try {
            $table = $courseData['table'];
            unset($courseData['table']);
            
            // Check if course exists
            $existingCourse = DB::table($table)->where('title', $courseData['title'])->first();
            
            if ($existingCourse) {
                echo "⚪ Course already exists: {$courseData['title']}\n";
                continue;
            }
            
            $courseId = DB::table($table)->insertGetId(array_merge($courseData, [
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]));
            
            echo "✅ Created course: {$courseData['title']} - ID: {$courseId}\n";
            
        } catch (Exception $e) {
            echo "❌ Failed to create course {$courseData['title']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 TEST USERS CREATED!\n";
    echo "======================\n\n";
    
    echo "🔐 LOGIN CREDENTIALS:\n";
    echo "=====================\n";
    echo "Student Account:\n";
    echo "  Email: student@test.com\n";
    echo "  Password: password123\n\n";
    
    echo "Admin Account:\n";
    echo "  Email: admin@test.com\n";
    echo "  Password: admin123\n\n";
    
    echo "Missouri Student:\n";
    echo "  Email: missouri@test.com\n";
    echo "  Password: password123\n\n";
    
    echo "Texas Student:\n";
    echo "  Email: texas@test.com\n";
    echo "  Password: password123\n\n";
    
    echo "🌐 LOGIN URL:\n";
    echo "=============\n";
    echo "http://nelly-elearning.test/login\n\n";
    
    echo "📋 AFTER LOGIN, TRY THESE:\n";
    echo "==========================\n";
    echo "- http://nelly-elearning.test/florida\n";
    echo "- http://nelly-elearning.test/missouri\n";
    echo "- http://nelly-elearning.test/texas\n";
    echo "- http://nelly-elearning.test/delaware\n";
    echo "- http://nelly-elearning.test/admin\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>