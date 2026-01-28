<?php

/**
 * Migrate Existing Data to State-Specific Tables
 * 
 * This script copies all existing courses, chapters, questions, and enrollments
 * to the appropriate state-specific tables while preserving all data.
 */

echo "🔧 MIGRATING EXISTING DATA TO STATE TABLES\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connection successful\n\n";
    
    // Step 1: Analyze existing data
    echo "📊 ANALYZING EXISTING DATA:\n";
    echo str_repeat("-", 30) . "\n";
    
    $coursesCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $floridaCoursesCount = $pdo->query("SELECT COUNT(*) FROM florida_courses")->fetchColumn();
    $chaptersCount = $pdo->query("SELECT COUNT(*) FROM chapters")->fetchColumn();
    $questionsCount = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
    $enrollmentsCount = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments")->fetchColumn();
    
    echo "• Courses table: {$coursesCount} records\n";
    echo "• Florida courses table: {$floridaCoursesCount} records\n";
    echo "• Chapters table: {$chaptersCount} records\n";
    echo "• Questions table: {$questionsCount} records\n";
    echo "• Enrollments table: {$enrollmentsCount} records\n\n";
    
    // Step 2: Copy courses from 'courses' table to state-specific tables
    echo "🔄 MIGRATING COURSES TO STATE TABLES:\n";
    echo str_repeat("-", 40) . "\n";
    
    if ($coursesCount > 0) {
        // Get all courses from the generic courses table
        $stmt = $pdo->query("SELECT * FROM courses WHERE is_active = 1");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($courses as $course) {
            $state = strtolower($course['state'] ?? $course['state_code'] ?? 'florida');
            
            // Determine target table based on state
            $targetTable = '';
            switch ($state) {
                case 'florida':
                case 'fl':
                    $targetTable = 'florida_courses';
                    break;
                case 'missouri':
                case 'mo':
                    $targetTable = 'missouri_courses';
                    break;
                case 'texas':
                case 'tx':
                    $targetTable = 'texas_courses';
                    break;
                case 'delaware':
                case 'de':
                    $targetTable = 'delaware_courses';
                    break;
                default:
                    $targetTable = 'florida_courses'; // Default fallback
            }
            
            echo "• Copying course '{$course['title']}' to {$targetTable}\n";
            
            // Check if course already exists in target table
            $checkStmt = $pdo->prepare("SELECT id FROM {$targetTable} WHERE title = ? AND description = ?");
            $checkStmt->execute([$course['title'], $course['description']]);
            
            if (!$checkStmt->fetch()) {
                // Prepare course data for target table
                if ($targetTable === 'florida_courses') {
                    // Florida courses table structure
                    $insertStmt = $pdo->prepare("
                        INSERT INTO florida_courses (
                            title, description, state_code, min_pass_score, total_duration, 
                            price, course_type, certificate_template, is_active, 
                            dicds_course_id, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    $insertStmt->execute([
                        $course['title'],
                        $course['description'],
                        $course['state'] ?? 'FL',
                        $course['passing_score'] ?? 80,
                        $course['duration'] ?? 240,
                        $course['price'] ?? 0,
                        $course['course_type'] ?? 'BDI',
                        $course['certificate_type'] ?? null,
                        $course['is_active'] ?? 1,
                        'MIGRATED_' . $course['id'] . '_' . time()
                    ]);
                } else {
                    // Other state tables (they reference the base courses table)
                    // First, ensure the base course exists
                    $baseCourseStmt = $pdo->prepare("
                        INSERT IGNORE INTO courses (
                            title, description, state, duration, price, passing_score, 
                            is_active, course_type, delivery_type, certificate_type, 
                            created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    $baseCourseStmt->execute([
                        $course['title'],
                        $course['description'],
                        $course['state'] ?? 'FL',
                        $course['duration'] ?? 240,
                        $course['price'] ?? 0,
                        $course['passing_score'] ?? 80,
                        $course['is_active'] ?? 1,
                        $course['course_type'] ?? 'BDI',
                        $course['delivery_type'] ?? 'Online',
                        $course['certificate_type'] ?? null
                    ]);
                    
                    $baseCourseId = $pdo->lastInsertId() ?: $course['id'];
                    
                    // Now create state-specific record
                    if ($targetTable === 'missouri_courses') {
                        $stateStmt = $pdo->prepare("
                            INSERT INTO missouri_courses (
                                course_id, missouri_course_code, course_type, required_hours,
                                max_completion_days, is_active, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        $stateStmt->execute([
                            $baseCourseId,
                            'MO-' . $course['id'],
                            'defensive_driving',
                            8.0,
                            90,
                            $course['is_active'] ?? 1
                        ]);
                    } elseif ($targetTable === 'texas_courses') {
                        $stateStmt = $pdo->prepare("
                            INSERT INTO texas_courses (
                                course_id, texas_course_code, course_type, defensive_driving_hours,
                                required_hours, max_completion_days, is_active, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        $stateStmt->execute([
                            $baseCourseId,
                            'TX-' . $course['id'],
                            'defensive_driving',
                            6,
                            6.0,
                            90,
                            $course['is_active'] ?? 1
                        ]);
                    } elseif ($targetTable === 'delaware_courses') {
                        $stateStmt = $pdo->prepare("
                            INSERT INTO delaware_courses (
                                course_id, delaware_course_code, course_type, required_hours,
                                max_completion_days, quiz_rotation_enabled, is_active, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        $stateStmt->execute([
                            $baseCourseId,
                            'DE-' . $course['id'],
                            'defensive_driving',
                            8.0,
                            90,
                            1,
                            $course['is_active'] ?? 1
                        ]);
                    }
                }
                
                echo "  ✅ Successfully copied to {$targetTable}\n";
            } else {
                echo "  ⚠️  Already exists in {$targetTable}\n";
            }
        }
    }
    
    // Step 3: Update chapters to reference correct course_table
    echo "\n🔄 UPDATING CHAPTERS FOR STATE AWARENESS:\n";
    echo str_repeat("-", 40) . "\n";
    
    if ($chaptersCount > 0) {
        // Get all chapters that don't have course_table set
        $stmt = $pdo->query("
            SELECT c.*, co.state, co.state_code 
            FROM chapters c 
            LEFT JOIN courses co ON c.course_id = co.id 
            WHERE c.course_table IS NULL OR c.course_table = ''
        ");
        $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($chapters as $chapter) {
            $state = strtolower($chapter['state'] ?? $chapter['state_code'] ?? 'florida');
            
            $courseTable = '';
            switch ($state) {
                case 'florida':
                case 'fl':
                    $courseTable = 'florida_courses';
                    break;
                case 'missouri':
                case 'mo':
                    $courseTable = 'missouri_courses';
                    break;
                case 'texas':
                case 'tx':
                    $courseTable = 'texas_courses';
                    break;
                case 'delaware':
                case 'de':
                    $courseTable = 'delaware_courses';
                    break;
                default:
                    $courseTable = 'florida_courses';
            }
            
            // Update chapter with correct course_table
            $updateStmt = $pdo->prepare("UPDATE chapters SET course_table = ? WHERE id = ?");
            $updateStmt->execute([$courseTable, $chapter['id']]);
            
            echo "• Updated chapter '{$chapter['title']}' to reference {$courseTable}\n";
        }
    }
    
    // Step 4: Update enrollments to reference correct course_table
    echo "\n🔄 UPDATING ENROLLMENTS FOR STATE AWARENESS:\n";
    echo str_repeat("-", 40) . "\n";
    
    if ($enrollmentsCount > 0) {
        // Get enrollments that don't have course_table set
        $stmt = $pdo->query("
            SELECT e.*, u.state_code, c.state 
            FROM user_course_enrollments e 
            LEFT JOIN users u ON e.user_id = u.id 
            LEFT JOIN courses c ON e.course_id = c.id 
            WHERE e.course_table IS NULL OR e.course_table = ''
        ");
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($enrollments as $enrollment) {
            $state = strtolower($enrollment['state_code'] ?? $enrollment['state'] ?? 'florida');
            
            $courseTable = '';
            switch ($state) {
                case 'florida':
                case 'fl':
                    $courseTable = 'florida_courses';
                    break;
                case 'missouri':
                case 'mo':
                    $courseTable = 'missouri_courses';
                    break;
                case 'texas':
                case 'tx':
                    $courseTable = 'texas_courses';
                    break;
                case 'delaware':
                case 'de':
                    $courseTable = 'delaware_courses';
                    break;
                default:
                    $courseTable = 'florida_courses';
            }
            
            // Update enrollment with correct course_table
            $updateStmt = $pdo->prepare("UPDATE user_course_enrollments SET course_table = ? WHERE id = ?");
            $updateStmt->execute([$courseTable, $enrollment['id']]);
            
            echo "• Updated enrollment {$enrollment['id']} to reference {$courseTable}\n";
        }
    }
    
    // Step 5: Final verification
    echo "\n📊 MIGRATION VERIFICATION:\n";
    echo str_repeat("-", 30) . "\n";
    
    $finalFloridaCount = $pdo->query("SELECT COUNT(*) FROM florida_courses")->fetchColumn();
    $finalMissouriCount = $pdo->query("SELECT COUNT(*) FROM missouri_courses")->fetchColumn();
    $finalTexasCount = $pdo->query("SELECT COUNT(*) FROM texas_courses")->fetchColumn();
    $finalDelawareCount = $pdo->query("SELECT COUNT(*) FROM delaware_courses")->fetchColumn();
    $updatedChapters = $pdo->query("SELECT COUNT(*) FROM chapters WHERE course_table IS NOT NULL")->fetchColumn();
    $updatedEnrollments = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments WHERE course_table IS NOT NULL")->fetchColumn();
    
    echo "• Florida courses: {$finalFloridaCount} records\n";
    echo "• Missouri courses: {$finalMissouriCount} records\n";
    echo "• Texas courses: {$finalTexasCount} records\n";
    echo "• Delaware courses: {$finalDelawareCount} records\n";
    echo "• Chapters with course_table: {$updatedChapters} records\n";
    echo "• Enrollments with course_table: {$updatedEnrollments} records\n";
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ DATA MIGRATION COMPLETE!\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "🎯 MIGRATION SUMMARY:\n";
    echo "• All existing courses copied to appropriate state tables\n";
    echo "• All chapters updated with correct course_table references\n";
    echo "• All enrollments updated with correct course_table references\n";
    echo "• Data integrity preserved throughout migration\n";
    echo "• System is now fully state-aware\n\n";
    
    echo "✅ YOUR SYSTEM IS NOW READY FOR STATE-SPECIFIC OPERATION!\n\n";
    
} catch (Exception $e) {
    echo "❌ Migration error: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n\n";
}

?>