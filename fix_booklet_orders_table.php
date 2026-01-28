<?php

/**
 * FIX BOOKLET ORDERS TABLE - Create missing booklet_orders table
 */

echo "🔧 FIXING BOOKLET ORDERS TABLE - Starting...\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n";
    
    // Check if booklet_orders table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'booklet_orders'");
    $stmt->execute();
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "✅ booklet_orders table already exists\n";
    } else {
        echo "🔧 Creating booklet_orders table...\n";
        
        $sql = "
        CREATE TABLE `booklet_orders` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `enrollment_id` bigint(20) unsigned NOT NULL,
            `user_id` bigint(20) unsigned NOT NULL,
            `booklet_id` bigint(20) unsigned DEFAULT NULL,
            `order_number` varchar(255) NOT NULL,
            `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
            `shipping_address` json DEFAULT NULL,
            `tracking_number` varchar(255) DEFAULT NULL,
            `cost` decimal(8,2) DEFAULT '0.00',
            `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
            `payment_method` varchar(50) DEFAULT NULL,
            `payment_id` varchar(255) DEFAULT NULL,
            `ordered_at` timestamp NULL DEFAULT NULL,
            `shipped_at` timestamp NULL DEFAULT NULL,
            `delivered_at` timestamp NULL DEFAULT NULL,
            `notes` text,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `booklet_orders_order_number_unique` (`order_number`),
            KEY `booklet_orders_enrollment_id_index` (`enrollment_id`),
            KEY `booklet_orders_user_id_index` (`user_id`),
            KEY `booklet_orders_booklet_id_index` (`booklet_id`),
            KEY `booklet_orders_status_index` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✅ booklet_orders table created successfully!\n";
    }
    
    // Check if booklets table exists (referenced by booklet_id)
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'booklets'");
    $stmt->execute();
    $bookletsExists = $stmt->fetch();
    
    if (!$bookletsExists) {
        echo "🔧 Creating booklets table...\n";
        
        $sql = "
        CREATE TABLE `booklets` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✅ booklets table created successfully!\n";
        
        // Add sample booklet data
        $pdo->exec("
            INSERT INTO booklets (title, description, course_id, course_table, state, price, is_active, pages, weight_oz, created_at, updated_at) VALUES
            ('Florida Driver Handbook', 'Official Florida driver handbook booklet', 1, 'florida_courses', 'FL', 15.99, 1, 120, 8.5, NOW(), NOW()),
            ('Missouri Driver Guide', 'Missouri driver improvement guide booklet', 1, 'missouri_courses', 'MO', 12.99, 1, 96, 6.8, NOW(), NOW()),
            ('Texas Driver Manual', 'Texas defensive driving manual', 1, 'texas_courses', 'TX', 14.99, 1, 108, 7.2, NOW(), NOW())
        ");
        echo "✅ Sample booklet data added\n";
    } else {
        echo "✅ booklets table already exists\n";
    }
    
    // Verify the fix by testing the original failing query
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
    
    // Check table counts
    echo "\n📊 Table verification:\n";
    
    $tables = ['booklet_orders', 'booklets'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        echo "✅ $table: $count records\n";
    }
    
    echo "\n🎉 BOOKLET ORDERS TABLE FIX COMPLETED!\n";
    echo "The booklets module should now work properly.\n";
    echo "\n🔗 Test the booklets module at:\n";
    echo "   • http://nelly-elearning.test/booklets\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>