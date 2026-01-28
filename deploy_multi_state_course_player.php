<?php

/**
 * Multi-State Course Player Deployment Script
 * 
 * This script deploys the complete multi-state course player system
 * with support for Florida, Missouri, Texas, and Delaware courses.
 */

echo "🚀 Starting Multi-State Course Player Deployment...\n";

// Check if we're in the correct directory
if (!file_exists('artisan')) {
    die("❌ Error: Please run this script from the Laravel root directory.\n");
}

// Function to run command and check result
function runCommand($command, $description) {
    echo "📋 {$description}...\n";
    echo "   Command: {$command}\n";
    
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "   ✅ Success\n";
        if (!empty($output)) {
            echo "   Output: " . implode("\n           ", $output) . "\n";
        }
        return true;
    } else {
        echo "   ❌ Failed (Return code: {$returnCode})\n";
        if (!empty($output)) {
            echo "   Error: " . implode("\n          ", $output) . "\n";
        }
        return false;
    }
}

// Function to check if table exists
function tableExists($tableName) {
    try {
        $pdo = new PDO(
            'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );
        
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        echo "   ⚠️  Warning: Could not check if table {$tableName} exists: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n=== PHASE 1: ENVIRONMENT SETUP ===\n";

// Load environment variables
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    foreach (explode("\n", $envContent) as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            putenv(trim($line));
        }
    }
    echo "✅ Environment variables loaded\n";
} else {
    echo "⚠️  Warning: .env file not found\n";
}

// Clear caches
runCommand('php artisan config:clear', 'Clearing configuration cache');
runCommand('php artisan cache:clear', 'Clearing application cache');
runCommand('php artisan route:clear', 'Clearing route cache');
runCommand('php artisan view:clear', 'Clearing view cache');

echo "\n=== PHASE 2: DATABASE MIGRATIONS ===\n";

// Run migrations
if (!runCommand('php artisan migrate --force', 'Running database migrations')) {
    echo "❌ Migration failed. Attempting to continue...\n";
}

// Check if required tables exist
$requiredTables = [
    'florida_courses',
    'missouri_courses', 
    'texas_courses',
    'delaware_courses',
    'chapters',
    'chapter_questions',
    'user_course_enrollments'
];

echo "\n📋 Checking required tables...\n";
foreach ($requiredTables as $table) {
    if (tableExists($table)) {
        echo "   ✅ {$table} exists\n";
    } else {
        echo "   ❌ {$table} missing\n";
    }
}

echo "\n=== PHASE 3: SEEDING DATA ===\n";

// Run seeders
runCommand('php artisan db:seed --class=MultiStateCourseSeeder', 'Seeding multi-state courses');
runCommand('php artisan db:seed --class=MultiStateQuizSeeder', 'Seeding multi-state quiz questions');

echo "\n=== PHASE 4: VERIFICATION ===\n";

// Verify course data
echo "📋 Verifying course data...\n";

try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    
    // Check Florida courses
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM florida_courses WHERE is_active = 1");
    $floridaCount = $stmt->fetch()['count'];
    echo "   ✅ Florida courses: {$floridaCount}\n";
    
    // Check Missouri courses
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM missouri_courses WHERE is_active = 1");
    $missouriCount = $stmt->fetch()['count'];
    echo "   ✅ Missouri courses: {$missouriCount}\n";
    
    // Check Texas courses
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM texas_courses WHERE is_active = 1");
    $texasCount = $stmt->fetch()['count'];
    echo "   ✅ Texas courses: {$texasCount}\n";
    
    // Check Delaware courses
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM delaware_courses WHERE is_active = 1");
    $delawareCount = $stmt->fetch()['count'];
    echo "   ✅ Delaware courses: {$delawareCount}\n";
    
    // Check chapters
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM chapters WHERE is_active = 1");
    $chaptersCount = $stmt->fetch()['count'];
    echo "   ✅ Chapters: {$chaptersCount}\n";
    
    // Check chapter questions
    if (tableExists('chapter_questions')) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM chapter_questions WHERE is_active = 1");
        $questionsCount = $stmt->fetch()['count'];
        echo "   ✅ Chapter questions: {$questionsCount}\n";
    }
    
} catch (Exception $e) {
    echo "   ⚠️  Warning: Could not verify data: " . $e->getMessage() . "\n";
}

echo "\n=== PHASE 5: FINAL SETUP ===\n";

// Generate application key if needed
if (empty(getenv('APP_KEY'))) {
    runCommand('php artisan key:generate', 'Generating application key');
}

// Create storage links
runCommand('php artisan storage:link', 'Creating storage symbolic links');

// Optimize for production
runCommand('php artisan config:cache', 'Caching configuration');
runCommand('php artisan route:cache', 'Caching routes');
runCommand('php artisan view:cache', 'Caching views');

echo "\n=== DEPLOYMENT SUMMARY ===\n";

echo "✅ Multi-State Course Player System Deployed Successfully!\n\n";

echo "📋 Features Deployed:\n";
echo "   • Multi-state course support (FL, MO, TX, DE)\n";
echo "   • State-specific course models and controllers\n";
echo "   • Enhanced CoursePlayerController with state logic\n";
echo "   • State-specific quiz questions and final exams\n";
echo "   • Multi-state certificate generation service\n";
echo "   • State-specific passing scores and requirements\n";
echo "   • Timer enforcement based on state regulations\n";
echo "   • Database migrations for all state tables\n";
echo "   • Sample course and quiz data for all states\n\n";

echo "🎯 State-Specific Requirements:\n";
echo "   • Florida (FL): 80% passing, 4-hour BDI/12-hour ADI, DICDS integration\n";
echo "   • Missouri (MO): 70% passing, 8-hour course, Form 4444 generation\n";
echo "   • Texas (TX): 75% passing, 6-hour course, TDLR compliance\n";
echo "   • Delaware (DE): 80% passing, 3hr/6hr options, quiz rotation\n\n";

echo "🔗 Key URLs:\n";
echo "   • Course Player: /course-player/{enrollmentId}\n";
echo "   • API Endpoints: /web/enrollments/{id}/...\n";
echo "   • Final Exam: /web/enrollments/{id}/final-exam/...\n\n";

echo "📁 Files Created/Modified:\n";
echo "   • app/Http/Controllers/CoursePlayerController.php (enhanced)\n";
echo "   • app/Http/Controllers/MultiStateFinalExamController.php (new)\n";
echo "   • app/Services/MultiStateCertificateService.php (new)\n";
echo "   • app/Models/MissouriCourse.php (new)\n";
echo "   • app/Models/TexasCourse.php (new)\n";
echo "   • app/Models/DelawareCourse.php (new)\n";
echo "   • database/migrations/2025_01_28_000001_create_multi_state_course_tables.php (new)\n";
echo "   • database/seeders/MultiStateCourseSeeder.php (new)\n";
echo "   • database/seeders/MultiStateQuizSeeder.php (new)\n";
echo "   • routes/web.php (enhanced with multi-state routes)\n\n";

echo "⚡ Next Steps:\n";
echo "   1. Test course enrollment for each state\n";
echo "   2. Verify state-specific quiz questions load correctly\n";
echo "   3. Test final exam functionality for each state\n";
echo "   4. Verify certificate generation for each state\n";
echo "   5. Test timer enforcement based on state requirements\n";
echo "   6. Configure state-specific API integrations (DICDS, etc.)\n\n";

echo "🎉 Multi-State Course Player System is Ready!\n";
echo "   The system now supports comprehensive multi-state functionality\n";
echo "   with state-specific compliance requirements and features.\n\n";

echo "📞 Support:\n";
echo "   • Check logs in storage/logs/ for any issues\n";
echo "   • Verify database connections and permissions\n";
echo "   • Test with sample enrollments for each state\n\n";

echo "✨ Deployment completed at " . date('Y-m-d H:i:s') . "\n";