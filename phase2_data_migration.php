<?php

/**
 * Phase 2: Data Migration to State Tables
 * 
 * Migrates existing courses and data to state-specific tables
 */

echo str_repeat("=", 60) . "\n";
echo "PHASE 2: DATA MIGRATION TO STATE TABLES\n";
echo str_repeat("=", 60) . "\n\n";

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Step 1: Analyze existing data
    echo "📊 ANALYZING EXISTING DATA:\n";
    
    $coursesCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $floridaCoursesCount = $pdo->query("SELECT COUNT(*) FROM florida_courses")->fetchColumn();
    $chaptersCount = $pdo->query("SELECT COUNT(*) FROM chapters")->fetchColumn();
    $enrollmentsCount = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments")->fetchColumn();
    
    echo "• Generic courses: {$coursesCount}\n";
    echo "• Florida courses: {$floridaCoursesCount}\n";
    echo "• Chapters: {$chaptersCount}\n";
    echo "• Enrollments: {$enrollmentsCount}\n\n";
    
    // Step 2: Migrate courses from generic table to state tables
    echo "🔄 MIGRATING COURSES TO STATE TABLES:\n";
    
    if ($coursesCount > 0) {
        $stmt = $pdo->query("SELECT * FROM courses WHERE is_active = 1");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($courses as $course) {
            $state = strtolower($course['state'] ?? $course['state_code'] ?? 'florida');
            
            echo "• Processing course: {$course['title']} (State: {$state})\n";
            
            // Determine target table and create state-specific record
            switch ($state) {
                case 'missouri':
                case 'mo':
                    $checkStmt = $pdo->prepare("SELECT id FROM missouri_courses WHERE course_id = ?");
                    $checkStmt->execute([$course['id']]);
                    
                    if (!$checkStmt->fetch()) {
                        $insertStmt = $pdo->prepare("
                            INSERT INTO missouri_courses (
                                course_id, missouri_course_code, course_type, required_hours,
                                max_completion_days, is_active, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        $insertStmt->execute([
                            $course['id'],
                            'MO-' . $course['id'],
                            'defensive_driving',
                            8.0,
                            90,
                            $course['is_active'] ?? 1
                        ]);
                        echo "  ✅ Added to missouri_courses\n";
                    }
                    break;
                    
                case 'texas':
                case 'tx':
                    $checkStmt = $pdo->prepare("SELECT id FROM texas_courses WHERE course_id = ?");
                    $checkStmt->execute([$course['id']]);
                    
                    if (!$checkStmt->fetch()) {
                        $insertStmt = $pdo->prepare("
                            INSERT INTO texas_courses (
                                course_id, texas_course_code, course_type, defensive_driving_hours,
                                required_hours, max_completion_days, is_active, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        $insertStmt->execute([
                            $course['id'],
                            'TX-' . $course['id'],
                            'defensive_driving',
                            6,
                            6.0,
                            90,
                            $course['is_active'] ?? 1
                        ]);
                        echo "  ✅ Added to texas_courses\n";
                    }
                    break;
                    
                case 'delaware':
                case 'de':
                    $checkStmt = $pdo->prepare("SELECT id FROM delaware_courses WHERE course_id = ?");
                    $checkStmt->execute([$course['id']]);
                    
                    if (!$checkStmt->fetch()) {
                        $insertStmt = $pdo->prepare("
                            INSERT INTO delaware_courses (
                                course_id, delaware_course_code, course_type, required_hours,
                                max_completion_days, quiz_rotation_enabled, is_active, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        $insertStmt->execute([
                            $course['id'],
                            'DE-' . $course['id'],
                            'defensive_driving',
                            8.0,
                            90,
                            1,
                            $course['is_active'] ?? 1
                        ]);
                        echo "  ✅ Added to delaware_courses\n";
                    }
                    break;
                    
                default:
                    // For Florida and others, ensure they're in florida_courses
                    $checkStmt = $pdo->prepare("SELECT id FROM florida_courses WHERE title = ? AND description = ?");
                    $checkStmt->execute([$course['title'], $course['description']]);
                    
                    if (!$checkStmt->fetch()) {
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
                        echo "  ✅ Added to florida_courses\n";
                    }
            }
        }
    }
    
    // Step 3: Update chapters with course_table references
    echo "\n🔄 UPDATING CHAPTERS FOR STATE AWARENESS:\n";
    
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
        
        $updateStmt = $pdo->prepare("UPDATE chapters SET course_table = ? WHERE id = ?");
        $updateStmt->execute([$courseTable, $chapter['id']]);
        
        echo "• Updated chapter '{$chapter['title']}' → {$courseTable}\n";
    }
    
    // Step 4: Update enrollments with course_table references
    echo "\n🔄 UPDATING ENROLLMENTS FOR STATE AWARENESS:\n";
    
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
        
        $updateStmt = $pdo->prepare("UPDATE user_course_enrollments SET course_table = ? WHERE id = ?");
        $updateStmt->execute([$courseTable, $enrollment['id']]);
        
        echo "• Updated enrollment {$enrollment['id']} → {$courseTable}\n";
    }
    
    // Step 5: Verification
    echo "\n📊 MIGRATION VERIFICATION:\n";
    
    $finalMissouriCount = $pdo->query("SELECT COUNT(*) FROM missouri_courses")->fetchColumn();
    $finalTexasCount = $pdo->query("SELECT COUNT(*) FROM texas_courses")->fetchColumn();
    $finalDelawareCount = $pdo->query("SELECT COUNT(*) FROM delaware_courses")->fetchColumn();
    $finalFloridaCount = $pdo->query("SELECT COUNT(*) FROM florida_courses")->fetchColumn();
    $updatedChapters = $pdo->query("SELECT COUNT(*) FROM chapters WHERE course_table IS NOT NULL")->fetchColumn();
    $updatedEnrollments = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments WHERE course_table IS NOT NULL")->fetchColumn();
    
    echo "• Missouri courses: {$finalMissouriCount}\n";
    echo "• Texas courses: {$finalTexasCount}\n";
    echo "• Delaware courses: {$finalDelawareCount}\n";
    echo "• Florida courses: {$finalFloridaCount}\n";
    echo "• Chapters with course_table: {$updatedChapters}\n";
    echo "• Enrollments with course_table: {$updatedEnrollments}\n";
    
    echo "\n✅ Phase 2 Complete: Data migration finished\n\n";
    
} catch (Exception $e) {
    echo "❌ Phase 2 Failed: " . $e->getMessage() . "\n";
    exit(1);
}

?>