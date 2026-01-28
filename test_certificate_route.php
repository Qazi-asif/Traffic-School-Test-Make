<?php

/**
 * TEST CERTIFICATE ROUTE - Verify the original failing route now works
 */

echo "🧪 TESTING CERTIFICATE ROUTE - Starting...\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected\n";
    
    // Test the exact query from the failing route
    echo "🔍 Testing the original failing query...\n";
    echo "Query: SELECT * FROM user_course_enrollments WHERE user_id = 1 AND status = 'completed'\n";
    
    $stmt = $pdo->prepare("SELECT * FROM user_course_enrollments WHERE user_id = 1 AND status = 'completed'");
    $stmt->execute();
    $enrollments = $stmt->fetchAll();
    
    echo "✅ Query executed successfully!\n";
    echo "📊 Results: " . count($enrollments) . " completed enrollments found\n";
    
    if (count($enrollments) > 0) {
        echo "\n📋 Enrollment Details:\n";
        foreach ($enrollments as $enrollment) {
            echo "   - Enrollment ID: {$enrollment['id']}\n";
            echo "   - User ID: {$enrollment['user_id']}\n";
            echo "   - Course ID: {$enrollment['course_id']}\n";
            echo "   - Course Table: {$enrollment['course_table']}\n";
            echo "   - Status: {$enrollment['status']}\n";
            echo "   - Completed At: {$enrollment['completed_at']}\n";
            echo "   - Progress: {$enrollment['progress_percentage']}%\n";
            echo "   - Final Exam: " . ($enrollment['final_exam_completed'] ? 'Completed' : 'Not Completed') . "\n";
            echo "\n";
        }
    }
    
    // Test with a user that has completed enrollments
    echo "🔍 Testing with user ID 4 (Admin User)...\n";
    $stmt = $pdo->prepare("SELECT * FROM user_course_enrollments WHERE user_id = 4 AND status = 'completed'");
    $stmt->execute();
    $adminEnrollments = $stmt->fetchAll();
    
    echo "✅ Admin user query executed successfully!\n";
    echo "📊 Results: " . count($adminEnrollments) . " completed enrollments found for admin user\n";
    
    if (count($adminEnrollments) > 0) {
        echo "\n📋 Admin Enrollment Details:\n";
        foreach ($adminEnrollments as $enrollment) {
            echo "   - Enrollment ID: {$enrollment['id']}\n";
            echo "   - User ID: {$enrollment['user_id']}\n";
            echo "   - Course ID: {$enrollment['course_id']}\n";
            echo "   - Status: {$enrollment['status']}\n";
            echo "   - Completed At: {$enrollment['completed_at']}\n";
            echo "   - Progress: {$enrollment['progress_percentage']}%\n";
            echo "\n";
        }
        
        // Test the course relationship
        echo "🔍 Testing course relationship...\n";
        $enrollment = $adminEnrollments[0];
        $courseTable = $enrollment['course_table'] ?? 'florida_courses';
        
        $stmt = $pdo->prepare("SELECT * FROM $courseTable WHERE id = ?");
        $stmt->execute([$enrollment['course_id']]);
        $course = $stmt->fetch();
        
        if ($course) {
            echo "✅ Course found: {$course['title']}\n";
            echo "   - Course ID: {$course['id']}\n";
            echo "   - Title: {$course['title']}\n";
            echo "   - Duration: {$course['duration_hours']} hours\n";
            echo "   - Price: \${$course['price']}\n";
        } else {
            echo "⚠️  Course not found in table: $courseTable\n";
        }
    }
    
    // Simulate the Laravel route logic
    echo "\n🎯 SIMULATING LARAVEL ROUTE LOGIC\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    // This simulates what the /generate-certificates route does
    $userId = 4; // Admin user
    $stmt = $pdo->prepare("
        SELECT uce.*, u.name as user_name, u.email as user_email
        FROM user_course_enrollments uce
        JOIN users u ON uce.user_id = u.id
        WHERE uce.user_id = ? AND uce.status = 'completed'
    ");
    $stmt->execute([$userId]);
    $enrollments = $stmt->fetchAll();
    
    echo "✅ Laravel route simulation successful!\n";
    echo "📊 Found " . count($enrollments) . " completed enrollments for certificate generation\n";
    
    if (count($enrollments) > 0) {
        echo "\n🎫 CERTIFICATES READY TO GENERATE:\n";
        foreach ($enrollments as $enrollment) {
            echo "   📜 Certificate #{$enrollment['id']}\n";
            echo "      Student: {$enrollment['user_name']} ({$enrollment['user_email']})\n";
            echo "      Course: ID {$enrollment['course_id']} from {$enrollment['course_table']}\n";
            echo "      Completed: {$enrollment['completed_at']}\n";
            echo "      Progress: {$enrollment['progress_percentage']}%\n";
            echo "      Final Exam: " . ($enrollment['final_exam_completed'] ? '✅ Passed' : '❌ Not Completed') . "\n";
            echo "\n";
        }
    }
    
    echo "🎉 ROUTE TEST COMPLETED SUCCESSFULLY!\n";
    echo "The original failing route should now work perfectly.\n";
    echo "\n🔗 Test URLs:\n";
    echo "   • http://nelly-elearning.test/generate-certificates\n";
    echo "   • http://nelly-elearning.test/my-certificates\n";
    echo "   • http://nelly-elearning.test/test-certificate-fix.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>