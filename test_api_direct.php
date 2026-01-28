<?php

/**
 * Direct API Test
 * 
 * Test the state distribution API directly to verify it's working
 */

echo "🧪 TESTING STATE DISTRIBUTION API\n";
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
    
    // Simulate the API endpoint logic
    $distribution = [];
    
    $states = [
        'florida' => 'florida_courses',
        'missouri' => 'missouri_courses',
        'texas' => 'texas_courses',
        'delaware' => 'delaware_courses',
        'nevada' => 'nevada_courses'
    ];
    
    echo "📊 STATE DISTRIBUTION RESULTS:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($states as $stateName => $table) {
        try {
            // Check if table exists
            $tableExists = $pdo->query("SHOW TABLES LIKE '{$table}'")->rowCount() > 0;
            
            if ($tableExists) {
                $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                
                try {
                    $enrollments = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments WHERE course_table = '{$table}'")->fetchColumn();
                } catch (Exception $e) {
                    $enrollments = 0;
                }
                
                $distribution[] = [
                    'state' => ucfirst($stateName),
                    'courses' => (int)$count,
                    'enrollments' => (int)$enrollments,
                    'table' => $table,
                    'status' => 'active'
                ];
                
                echo "✅ {$stateName}: {$count} courses, {$enrollments} enrollments\n";
            } else {
                $distribution[] = [
                    'state' => ucfirst($stateName),
                    'courses' => 0,
                    'enrollments' => 0,
                    'table' => $table,
                    'status' => 'table_missing'
                ];
                
                echo "❌ {$stateName}: Table missing\n";
            }
        } catch (Exception $e) {
            $distribution[] = [
                'state' => ucfirst($stateName),
                'courses' => 0,
                'enrollments' => 0,
                'table' => $table,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
            
            echo "⚠️  {$stateName}: Error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📋 API RESPONSE (JSON):\n";
    echo str_repeat("-", 50) . "\n";
    echo json_encode($distribution, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test course listing
    echo "📚 UNIFIED COURSE LISTING TEST:\n";
    echo str_repeat("-", 50) . "\n";
    
    $allCourses = [];
    
    // Query Florida courses
    try {
        $floridaCourses = $pdo->query("SELECT * FROM florida_courses LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($floridaCourses as $course) {
            $allCourses[] = [
                'id' => $course['id'],
                'title' => $course['title'],
                'state_code' => $course['state_code'] ?? 'FL',
                'table' => 'florida_courses',
                'state_name' => 'Florida',
                'price' => $course['price'] ?? 0
            ];
        }
        
        echo "✅ Florida courses loaded: " . count($floridaCourses) . "\n";
    } catch (Exception $e) {
        echo "❌ Error loading Florida courses: " . $e->getMessage() . "\n";
    }
    
    // Query other state tables
    foreach (['missouri_courses', 'texas_courses', 'delaware_courses', 'nevada_courses'] as $table) {
        try {
            $tableExists = $pdo->query("SHOW TABLES LIKE '{$table}'")->rowCount() > 0;
            
            if ($tableExists) {
                $courses = $pdo->query("SELECT * FROM {$table} LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
                
                $stateName = ucfirst(str_replace('_courses', '', $table));
                
                foreach ($courses as $course) {
                    $allCourses[] = [
                        'id' => $course['id'],
                        'title' => $stateName . ' Course #' . $course['id'],
                        'state_code' => substr($stateName, 0, 2),
                        'table' => $table,
                        'state_name' => $stateName,
                        'price' => 0
                    ];
                }
                
                echo "✅ {$stateName} courses loaded: " . count($courses) . "\n";
            }
        } catch (Exception $e) {
            // Table might not exist or have data yet
        }
    }
    
    echo "\n📋 UNIFIED COURSE LISTING (Sample):\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach (array_slice($allCourses, 0, 5) as $course) {
        echo "• [{$course['state_name']}] {$course['title']} (${$course['price']})\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 API TEST COMPLETE!\n";
    echo "Total courses found: " . count($allCourses) . "\n";
    echo "States with data: " . count(array_filter($distribution, function($d) { return $d['courses'] > 0; })) . "\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "🌐 TEST YOUR LIVE API:\n";
    echo "Visit: http://nelly-elearning.test/api/admin/analytics/state-distribution\n";
    echo "Visit: http://nelly-elearning.test/api/courses\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>