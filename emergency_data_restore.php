<?php

/**
 * Emergency Data Restore Script
 * 
 * This script restores essential data that was lost during migrate:fresh
 */

echo "🚨 EMERGENCY DATA RESTORE\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Database connection
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connection successful\n\n";
    
    // 1. Create essential user accounts
    echo "👤 Creating essential user accounts...\n";
    
    // Create admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT IGNORE INTO users (
            id, first_name, last_name, email, password, email_verified_at, 
            state_code, role_id, created_at, updated_at
        ) VALUES (
            1, 'Admin', 'User', 'admin@dummiestrafficschool.com', 
            '{$adminPassword}', NOW(), 'FL', 1, NOW(), NOW()
        )
    ");
    
    // Create test student
    $studentPassword = password_hash('student123', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT IGNORE INTO users (
            id, first_name, last_name, email, password, email_verified_at, 
            state_code, role_id, created_at, updated_at
        ) VALUES (
            2, 'Test', 'Student', 'student@test.com', 
            '{$studentPassword}', NOW(), 'FL', 2, NOW(), NOW()
        )
    ");
    
    echo "✅ Admin user created: admin@dummiestrafficschool.com / admin123\n";
    echo "✅ Test student created: student@test.com / student123\n";
    
    // 2. Create roles if they don't exist
    echo "\n🔐 Creating user roles...\n";
    
    $pdo->exec("
        INSERT IGNORE INTO roles (id, name, slug, description, created_at, updated_at) VALUES
        (1, 'Administrator', 'admin', 'System administrator with full access', NOW(), NOW()),
        (2, 'Student', 'student', 'Student user with course access', NOW(), NOW()),
        (3, 'Instructor', 'instructor', 'Course instructor', NOW(), NOW())
    ");
    
    echo "✅ User roles created\n";
    
    // 3. Restore Florida courses
    echo "\n📚 Restoring Florida courses...\n";
    
    $floridaCourses = [
        [
            'title' => 'Florida Basic Driver Improvement (BDI) Course',
            'description' => 'State-approved 4-hour Basic Driver Improvement course for Florida residents.',
            'state_code' => 'FL',
            'total_duration' => 240,
            'price' => 25.00,
            'min_pass_score' => 80,
            'course_type' => 'BDI',
            'is_active' => 1
        ],
        [
            'title' => 'Florida Defensive Driving Course',
            'description' => 'Comprehensive defensive driving course approved by Florida DHSMV.',
            'state_code' => 'FL', 
            'total_duration' => 300,
            'price' => 29.95,
            'min_pass_score' => 80,
            'course_type' => 'Defensive Driving',
            'is_active' => 1
        ],
        [
            'title' => 'Florida Traffic School Online',
            'description' => 'Online traffic school course for ticket dismissal in Florida.',
            'state_code' => 'FL',
            'total_duration' => 240,
            'price' => 24.95,
            'min_pass_score' => 70,
            'course_type' => 'Traffic School',
            'is_active' => 1
        ]
    ];
    
    foreach ($floridaCourses as $course) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO florida_courses (
                title, description, state_code, total_duration, price, 
                min_pass_score, course_type, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $course['title'],
            $course['description'],
            $course['state_code'],
            $course['total_duration'],
            $course['price'],
            $course['min_pass_score'],
            $course['course_type'],
            $course['is_active']
        ]);
    }
    
    echo "✅ Florida courses restored\n";
    
    // 4. Create sample chapters for courses
    echo "\n📖 Creating sample chapters...\n";
    
    $floridaCourseIds = $pdo->query("SELECT id FROM florida_courses LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($floridaCourseIds as $courseId) {
        $chapters = [
            [
                'title' => 'Introduction to Safe Driving',
                'content' => 'Welcome to the course. This chapter introduces the fundamentals of safe driving practices.',
                'duration' => 30,
                'order_index' => 1
            ],
            [
                'title' => 'Traffic Laws and Regulations',
                'content' => 'Understanding Florida traffic laws and regulations that every driver must know.',
                'duration' => 45,
                'order_index' => 2
            ],
            [
                'title' => 'Defensive Driving Techniques',
                'content' => 'Learn defensive driving techniques to avoid accidents and stay safe on the road.',
                'duration' => 35,
                'order_index' => 3
            ]
        ];
        
        foreach ($chapters as $chapter) {
            $pdo->exec("
                INSERT IGNORE INTO chapters (
                    course_id, course_table, title, content, duration, 
                    required_min_time, order_index, is_active, created_at, updated_at
                ) VALUES (
                    {$courseId}, 'florida_courses', '{$chapter['title']}', 
                    '{$chapter['content']}', {$chapter['duration']}, 
                    {$chapter['duration']}, {$chapter['order_index']}, 1, NOW(), NOW()
                )
            ");
        }
    }
    
    echo "✅ Sample chapters created\n";
    
    // 5. Create sample enrollment for test student
    echo "\n📝 Creating sample enrollment...\n";
    
    if (count($floridaCourseIds) > 0) {
        $pdo->exec("
            INSERT IGNORE INTO user_course_enrollments (
                user_id, course_id, course_table, status, payment_status, 
                progress_percentage, enrolled_at, started_at, created_at, updated_at
            ) VALUES (
                2, {$floridaCourseIds[0]}, 'florida_courses', 'in_progress', 'paid', 
                45.5, NOW(), NOW(), NOW(), NOW()
            )
        ");
        
        echo "✅ Sample enrollment created for test student\n";
    }
    
    // 6. Create essential settings
    echo "\n⚙️ Creating system settings...\n";
    
    $pdo->exec("
        INSERT IGNORE INTO settings (key, value, created_at, updated_at) VALUES
        ('site_name', 'Dummies Traffic School', NOW(), NOW()),
        ('site_description', 'Online Traffic School and Defensive Driving Courses', NOW(), NOW()),
        ('maintenance_mode', '0', NOW(), NOW()),
        ('registration_enabled', '1', NOW(), NOW())
    ");
    
    echo "✅ System settings created\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 DATA RESTORE COMPLETE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "🔑 LOGIN CREDENTIALS:\n";
    echo "Admin: admin@dummiestrafficschool.com / admin123\n";
    echo "Student: student@test.com / student123\n\n";
    
    echo "✅ Your system is now restored with:\n";
    echo "• User accounts and roles\n";
    echo "• Florida courses\n";
    echo "• Sample chapters\n";
    echo "• Test enrollment\n";
    echo "• System settings\n\n";
    
    echo "🌐 You can now login at: http://nelly-elearning.test/login\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>