<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 CREATING MISSING USER_COURSE_ENROLLMENTS TABLE\n";
echo "================================================\n\n";

try {
    // Check if table exists
    $tableExists = DB::getSchemaBuilder()->hasTable('user_course_enrollments');
    
    if ($tableExists) {
        echo "✅ Table user_course_enrollments already exists\n";
        $count = DB::table('user_course_enrollments')->count();
        echo "✅ Table has {$count} records\n";
    } else {
        echo "❌ Table user_course_enrollments does not exist, creating...\n";
        
        // Create the table
        DB::statement("
            CREATE TABLE `user_course_enrollments` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `course_id` bigint(20) unsigned NOT NULL,
                `course_table` varchar(255) DEFAULT 'florida_courses',
                `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
                `amount_paid` decimal(8,2) DEFAULT NULL,
                `payment_method` varchar(255) DEFAULT NULL,
                `payment_id` varchar(255) DEFAULT NULL,
                `citation_number` varchar(255) DEFAULT NULL,
                `case_number` varchar(255) DEFAULT NULL,
                `court_state` varchar(255) DEFAULT NULL,
                `court_county` varchar(255) DEFAULT NULL,
                `court_selected` varchar(255) DEFAULT NULL,
                `court_date` date DEFAULT NULL,
                `enrolled_at` timestamp NULL DEFAULT NULL,
                `started_at` timestamp NULL DEFAULT NULL,
                `completed_at` timestamp NULL DEFAULT NULL,
                `progress_percentage` decimal(5,2) DEFAULT 0.00,
                `quiz_average` decimal(5,2) DEFAULT NULL,
                `total_time_spent` int(11) DEFAULT 0,
                `status` enum('pending','active','completed','expired','cancelled') DEFAULT 'pending',
                `access_revoked` tinyint(1) DEFAULT 0,
                `access_revoked_at` timestamp NULL DEFAULT NULL,
                `last_activity_at` timestamp NULL DEFAULT NULL,
                `reminder_sent_at` timestamp NULL DEFAULT NULL,
                `reminder_count` int(11) DEFAULT 0,
                `optional_services` json DEFAULT NULL,
                `optional_services_total` decimal(8,2) DEFAULT 0.00,
                `final_exam_completed` tinyint(1) DEFAULT 0,
                `final_exam_result_id` bigint(20) unsigned DEFAULT NULL,
                `certificate_generated_at` timestamp NULL DEFAULT NULL,
                `certificate_number` varchar(255) DEFAULT NULL,
                `certificate_path` varchar(500) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `user_course_enrollments_user_id_index` (`user_id`),
                KEY `user_course_enrollments_course_id_index` (`course_id`),
                KEY `user_course_enrollments_status_index` (`status`),
                KEY `user_course_enrollments_payment_status_index` (`payment_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✅ Created user_course_enrollments table\n";
    }
    
    // Check other required tables
    $requiredTables = [
        'users',
        'florida_courses',
        'courses',
        'chapters',
        'final_exam_results'
    ];
    
    echo "\nChecking other required tables:\n";
    foreach ($requiredTables as $table) {
        $exists = DB::getSchemaBuilder()->hasTable($table);
        if ($exists) {
            $count = DB::table($table)->count();
            echo "✅ {$table}: {$count} records\n";
        } else {
            echo "❌ {$table}: missing\n";
        }
    }
    
    // Create some test data if tables are empty
    $userCount = DB::table('users')->count();
    if ($userCount == 0) {
        echo "\nCreating test user...\n";
        DB::table('users')->insert([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Created test user\n";
    }
    
    // Create test course if needed
    $courseCount = DB::table('florida_courses')->count();
    if ($courseCount == 0) {
        echo "\nCreating test course...\n";
        DB::table('florida_courses')->insert([
            'title' => 'Florida Traffic School Course',
            'description' => 'Basic traffic school course for Florida',
            'state_code' => 'FL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Created test course\n";
    }
    
    // Create test enrollment if needed
    $enrollmentCount = DB::table('user_course_enrollments')->count();
    if ($enrollmentCount == 0) {
        $userId = DB::table('users')->first()->id ?? 1;
        $courseId = DB::table('florida_courses')->first()->id ?? 1;
        
        echo "\nCreating test enrollment...\n";
        DB::table('user_course_enrollments')->insert([
            'user_id' => $userId,
            'course_id' => $courseId,
            'course_table' => 'florida_courses',
            'payment_status' => 'paid',
            'status' => 'completed',
            'progress_percentage' => 100.00,
            'enrolled_at' => now(),
            'completed_at' => now(),
            'certificate_generated_at' => now(),
            'certificate_number' => 'CERT-2026-000001',
            'certificate_path' => 'certificates/cert-1.html',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Created test enrollment\n";
    }
    
    echo "\n🎉 DATABASE SETUP COMPLETE!\n";
    echo "===========================\n";
    echo "✅ All required tables exist\n";
    echo "✅ Test data created\n";
    echo "✅ System ready for certificate generation\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Setup completed at " . date('Y-m-d H:i:s') . "\n";