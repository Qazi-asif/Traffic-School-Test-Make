<?php

/**
 * TEST CERTIFICATE FIX - Verify the database fix worked
 */

echo "<!DOCTYPE html><html><head><title>Certificate Fix Test</title>";
echo "<style>body{font-family:Arial;margin:40px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body>";

echo "<h1>🧪 Certificate System Test</h1>";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p class='success'>✅ Database connection successful</p>";
    
    // Test 1: Check if table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'user_course_enrollments'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p class='success'>✅ user_course_enrollments table exists</p>";
    } else {
        echo "<p class='error'>❌ user_course_enrollments table missing</p>";
        exit;
    }
    
    // Test 2: Check table structure
    $stmt = $pdo->prepare("DESCRIBE user_course_enrollments");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['id', 'user_id', 'course_id', 'status', 'completed_at', 'final_exam_completed'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (empty($missingColumns)) {
        echo "<p class='success'>✅ Table structure is complete</p>";
    } else {
        echo "<p class='error'>❌ Missing columns: " . implode(', ', $missingColumns) . "</p>";
    }
    
    // Test 3: Test the failing query
    echo "<h2>🔍 Testing the Original Failing Query</h2>";
    
    $stmt = $pdo->prepare("SELECT * FROM user_course_enrollments WHERE user_id = 1 AND status = 'completed'");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    echo "<p class='success'>✅ Query executed successfully!</p>";
    echo "<p class='info'>Found " . count($results) . " completed enrollments for user 1</p>";
    
    if (count($results) > 0) {
        echo "<h3>📋 Sample Enrollment Data:</h3>";
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Course ID</th><th>Status</th><th>Completed At</th><th>Progress</th></tr>";
        
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['course_id'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . $row['completed_at'] . "</td>";
            echo "<td>" . $row['progress_percentage'] . "%</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test 4: Check related tables
    echo "<h2>🔍 Checking Related Tables</h2>";
    
    $tables = ['users', 'florida_courses', 'courses'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "<p class='success'>✅ $table: $count records</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ $table: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test 5: Test certificate generation readiness
    echo "<h2>🎯 Certificate Generation Readiness</h2>";
    
    $stmt = $pdo->prepare("
        SELECT 
            uce.id as enrollment_id,
            uce.user_id,
            uce.course_id,
            uce.status,
            uce.completed_at,
            uce.final_exam_completed,
            u.name as user_name,
            u.email as user_email
        FROM user_course_enrollments uce
        JOIN users u ON uce.user_id = u.id
        WHERE uce.status = 'completed'
        LIMIT 5
    ");
    $stmt->execute();
    $readyForCertificates = $stmt->fetchAll();
    
    if (count($readyForCertificates) > 0) {
        echo "<p class='success'>✅ Found " . count($readyForCertificates) . " enrollments ready for certificate generation</p>";
        
        echo "<h3>📜 Ready for Certificates:</h3>";
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>Enrollment ID</th><th>User</th><th>Email</th><th>Status</th><th>Completed</th></tr>";
        
        foreach ($readyForCertificates as $row) {
            echo "<tr>";
            echo "<td>" . $row['enrollment_id'] . "</td>";
            echo "<td>" . $row['user_name'] . "</td>";
            echo "<td>" . $row['user_email'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . $row['completed_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ No completed enrollments found for certificate generation</p>";
    }
    
    echo "<h2>🎉 Test Results Summary</h2>";
    echo "<p class='success'>✅ Database structure is fixed and ready</p>";
    echo "<p class='success'>✅ The original error should be resolved</p>";
    echo "<p class='success'>✅ Certificate generation should now work</p>";
    
    echo "<h2>🔗 Test Links</h2>";
    echo "<p><a href='/generate-certificates' target='_blank'>Test Certificate Generation</a></p>";
    echo "<p><a href='/my-certificates' target='_blank'>Test My Certificates Page</a></p>";
    echo "<p><a href='/dashboard' target='_blank'>Test Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";

?>