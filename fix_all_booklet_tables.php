<?php

/**
 * FIX ALL BOOKLET TABLES - Create all missing booklet-related tables
 */

echo "🔧 FIXING ALL BOOKLET TABLES - Starting comprehensive fix...\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n\n";
    
    // Define all booklet-related tables
    $bookletTables = [
        'course_booklets' => "
            CREATE TABLE IF NOT EXISTS `course_booklets` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `course_table` varchar(255) DEFAULT 'courses',
                `title` varchar(255) NOT NULL,
                `description` text,
                `pdf_template_path` varchar(500) DEFAULT NULL,
                `cover_image_path` varchar(500) DEFAULT NULL,
                `price_pdf` decimal(8,2) DEFAULT '0.00',
                `price_print` decimal(8,2) DEFAULT '15.99',
                `pages` int(11) DEFAULT '0',
                `weight_oz` decimal(5,2) DEFAULT '8.00',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `course_booklets_course_id_index` (`course_id`),
                KEY `course_booklets_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'booklet_orders' => "
            CREATE TABLE IF NOT EXISTS `booklet_orders` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `enrollment_id` bigint(20) unsigned NOT NULL,
                `user_id` bigint(20) unsigned NOT NULL,
                `booklet_id` bigint(20) unsigned DEFAULT NULL,
                `order_number` varchar(255) NOT NULL,
                `format` enum('pdf_download','print_mail','print_pickup') DEFAULT 'pdf_download',
                `status` enum('pending','processing','ready','shipped','delivered','cancelled') DEFAULT 'pending',
                `shipping_address` json DEFAULT NULL,
                `tracking_number` varchar(255) DEFAULT NULL,
                `cost` decimal(8,2) DEFAULT '0.00',
                `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
                `payment_method` varchar(50) DEFAULT NULL,
                `payment_id` varchar(255) DEFAULT NULL,
                `file_path` varchar(500) DEFAULT NULL,
                `ordered_at` timestamp NULL DEFAULT NULL,
                `processed_at` timestamp NULL DEFAULT NULL,
                `shipped_at` timestamp NULL DEFAULT NULL,
                `delivered_at` timestamp NULL DEFAULT NULL,
                `downloaded_at` timestamp NULL DEFAULT NULL,
                `download_count` int(11) DEFAULT '0',
                `notes` text,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `booklet_orders_order_number_unique` (`order_number`),
                KEY `booklet_orders_enrollment_id_index` (`enrollment_id`),
                KEY `booklet_orders_user_id_index` (`user_id`),
                KEY `booklet_orders_booklet_id_index` (`booklet_id`),
                KEY `booklet_orders_status_index` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'booklets' => "
            CREATE TABLE IF NOT EXISTS `booklets` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `description` text,
                `course_id` bigint(20) unsigned DEFAULT NULL,
                `course_table` varchar(255) DEFAULT 'courses',
                `state` varchar(2) DEFAULT NULL,
                `price` decimal(8,2) DEFAULT '0.00',
                `is_active` tinyint(1) DEFAULT '1',
                `pdf_path` varchar(500) DEFAULT NULL,
                `cover_image` varchar(500) DEFAULT NULL,
                `pages` int(11) DEFAULT '0',
                `weight_oz` decimal(5,2) DEFAULT '0.00',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `booklets_course_id_index` (`course_id`),
                KEY `booklets_state_index` (`state`),
                KEY `booklets_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'jobs' => "
            CREATE TABLE IF NOT EXISTS `jobs` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `queue` varchar(255) NOT NULL,
                `payload` longtext NOT NULL,
                `attempts` tinyint(3) unsigned NOT NULL,
                `reserved_at` int(10) unsigned DEFAULT NULL,
                `available_at` int(10) unsigned NOT NULL,
                `created_at` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                KEY `jobs_queue_index` (`queue`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'failed_jobs' => "
            CREATE TABLE IF NOT EXISTS `failed_jobs` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `uuid` varchar(255) NOT NULL,
                `connection` text NOT NULL,
                `queue` text NOT NULL,
                `payload` longtext NOT NULL,
                `exception` longtext NOT NULL,
                `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    echo "🔍 Checking and creating " . count($bookletTables) . " booklet-related tables...\n\n";
    
    $created = [];
    $existing = [];
    $errors = [];
    
    foreach ($bookletTables as $tableName => $sql) {
        echo "Checking table: $tableName... ";
        
        // Check if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE '$tableName'");
        $stmt->execute();
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "✅ EXISTS\n";
            $existing[] = $tableName;
        } else {
            echo "❌ MISSING - Creating... ";
            try {
                $pdo->exec($sql);
                echo "✅ CREATED\n";
                $created[] = $tableName;
            } catch (Exception $e) {
                echo "❌ FAILED: " . $e->getMessage() . "\n";
                $errors[] = "$tableName: " . $e->getMessage();
            }
        }
    }
    
    echo "\n📊 SUMMARY:\n";
    echo "✅ Tables already existing: " . count($existing) . "\n";
    echo "🆕 Tables created: " . count($created) . "\n";
    echo "❌ Errors: " . count($errors) . "\n\n";
    
    if (!empty($created)) {
        echo "🆕 CREATED TABLES:\n";
        foreach ($created as $table) {
            echo "   • $table\n";
        }
        echo "\n";
    }
    
    // Add sample data for course_booklets
    echo "🔧 Adding sample booklet data...\n";
    
    // Check if we have course_booklets data
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM course_booklets");
    $stmt->execute();
    $bookletCount = $stmt->fetch()['count'];
    
    if ($bookletCount == 0) {
        // Get available courses
        $stmt = $pdo->prepare("SELECT id, title FROM florida_courses LIMIT 1");
        $stmt->execute();
        $floridaCourse = $stmt->fetch();
        
        if ($floridaCourse) {
            $pdo->exec("
                INSERT INTO course_booklets (course_id, course_table, title, description, price_pdf, price_print, pages, weight_oz, is_active, created_at, updated_at) VALUES
                ({$floridaCourse['id']}, 'florida_courses', 'Florida Driver Handbook', 'Official Florida driver handbook with course materials', 9.99, 19.99, 120, 8.5, 1, NOW(), NOW())
            ");
            echo "✅ Added Florida course booklet\n";
        }
        
        $stmt = $pdo->prepare("SELECT id, title FROM missouri_courses LIMIT 1");
        $stmt->execute();
        $missouriCourse = $stmt->fetch();
        
        if ($missouriCourse) {
            $pdo->exec("
                INSERT INTO course_booklets (course_id, course_table, title, description, price_pdf, price_print, pages, weight_oz, is_active, created_at, updated_at) VALUES
                ({$missouriCourse['id']}, 'missouri_courses', 'Missouri Driver Guide', 'Missouri driver improvement guide with course materials', 8.99, 17.99, 96, 6.8, 1, NOW(), NOW())
            ");
            echo "✅ Added Missouri course booklet\n";
        }
        
        $stmt = $pdo->prepare("SELECT id, title FROM courses LIMIT 1");
        $stmt->execute();
        $genericCourse = $stmt->fetch();
        
        if ($genericCourse) {
            $pdo->exec("
                INSERT INTO course_booklets (course_id, course_table, title, description, price_pdf, price_print, pages, weight_oz, is_active, created_at, updated_at) VALUES
                ({$genericCourse['id']}, 'courses', 'Traffic School Handbook', 'Generic traffic school handbook with course materials', 7.99, 15.99, 80, 5.5, 1, NOW(), NOW())
            ");
            echo "✅ Added generic course booklet\n";
        }
    } else {
        echo "✅ Course booklets already have data ($bookletCount records)\n";
    }
    
    // Test the original failing query
    echo "\n🧪 Testing the original failing query...\n";
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as aggregate 
        FROM booklet_orders 
        WHERE EXISTS (
            SELECT * FROM user_course_enrollments 
            WHERE booklet_orders.enrollment_id = user_course_enrollments.id 
            AND user_id = 1
        )
    ");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "✅ Query executed successfully! Found " . $result['aggregate'] . " booklet orders\n";
    
    // Verify all tables
    echo "\n📊 Final table verification:\n";
    
    foreach (array_keys($bookletTables) as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
        } catch (Exception $e) {
            echo "❌ $table: Error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 ALL BOOKLET TABLES FIX COMPLETED!\n";
    echo "The booklets module should now be fully operational.\n";
    echo "\n🔗 Test the booklets module at:\n";
    echo "   • http://nelly-elearning.test/booklets\n";
    echo "   • http://nelly-elearning.test/booklets/order/{enrollment_id}\n";
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>