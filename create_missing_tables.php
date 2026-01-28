<?php

/**
 * CREATE MISSING TABLES - Complete the database structure
 */

echo "🔧 CREATING MISSING TABLES - Starting...\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n";
    
    // Create courses table
    echo "🔧 Creating courses table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `courses` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `duration_hours` int(11) DEFAULT '4',
            `price` decimal(8,2) DEFAULT '29.95',
            `state` varchar(2) DEFAULT 'FL',
            `is_active` tinyint(1) DEFAULT '1',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `courses_state_index` (`state`),
            KEY `courses_is_active_index` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ courses table created\n";
    
    // Create chapters table
    echo "🔧 Creating chapters table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `chapters` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `course_id` bigint(20) unsigned NOT NULL,
            `course_table` varchar(255) DEFAULT 'courses',
            `title` varchar(255) NOT NULL,
            `content` longtext,
            `order_index` int(11) DEFAULT '0',
            `duration_minutes` int(11) DEFAULT '30',
            `is_active` tinyint(1) DEFAULT '1',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `chapters_course_id_index` (`course_id`),
            KEY `chapters_order_index_index` (`order_index`),
            KEY `chapters_is_active_index` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ chapters table created\n";
    
    // Create chapter_questions table
    echo "🔧 Creating chapter_questions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `chapter_questions` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `chapter_id` bigint(20) unsigned NOT NULL,
            `question_text` text NOT NULL,
            `option_a` varchar(500) DEFAULT NULL,
            `option_b` varchar(500) DEFAULT NULL,
            `option_c` varchar(500) DEFAULT NULL,
            `option_d` varchar(500) DEFAULT NULL,
            `correct_answer` enum('A','B','C','D') NOT NULL,
            `explanation` text,
            `order_index` int(11) DEFAULT '0',
            `is_active` tinyint(1) DEFAULT '1',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `chapter_questions_chapter_id_index` (`chapter_id`),
            KEY `chapter_questions_order_index_index` (`order_index`),
            KEY `chapter_questions_is_active_index` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ chapter_questions table created\n";
    
    // Add some test data
    echo "🔧 Adding test data...\n";
    
    // Add test course
    $pdo->exec("
        INSERT IGNORE INTO courses (id, title, description, duration_hours, price, state, is_active, created_at, updated_at) 
        VALUES (1, 'Generic Traffic School Course', 'Basic traffic school course', 4, 29.95, 'FL', 1, NOW(), NOW())
    ");
    
    // Add test chapter
    $pdo->exec("
        INSERT IGNORE INTO chapters (id, course_id, course_table, title, content, order_index, duration_minutes, is_active, created_at, updated_at) 
        VALUES (1, 1, 'courses', 'Chapter 1: Traffic Laws', '<h2>Traffic Laws</h2><p>This chapter covers basic traffic laws.</p>', 1, 30, 1, NOW(), NOW())
    ");
    
    // Add test questions
    $pdo->exec("
        INSERT IGNORE INTO chapter_questions (id, chapter_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation, order_index, is_active, created_at, updated_at) 
        VALUES 
        (1, 1, 'What is the speed limit in a school zone?', '15 mph', '25 mph', '35 mph', '45 mph', 'B', 'School zones typically have a 25 mph speed limit.', 1, 1, NOW(), NOW()),
        (2, 1, 'When should you use your turn signal?', 'Only when turning left', 'Only when turning right', 'When changing lanes or turning', 'Never', 'C', 'Turn signals should be used when changing lanes or turning.', 2, 1, NOW(), NOW())
    ");
    
    echo "✅ Test data added\n";
    
    // Verify tables exist
    echo "\n🔍 Verifying tables...\n";
    $tables = ['courses', 'chapters', 'chapter_questions'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE '$table'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
        } else {
            echo "❌ $table: not found\n";
        }
    }
    
    echo "\n🎉 MISSING TABLES CREATED SUCCESSFULLY!\n";
    echo "All required database tables are now in place.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>