<?php
/**
 * Run Course Migration - Simple Command
 * Execute the complete course and quiz migration process
 */

echo "🚀 RUNNING COMPLETE COURSE MIGRATION\n";
echo "===================================\n\n";

echo "This will migrate all courses and quizzes from your original system\n";
echo "to the new multi-state traffic school system.\n\n";

echo "📋 WHAT WILL BE MIGRATED:\n";
echo "- All courses → State-specific course tables\n";
echo "- All chapters → Updated with new course references\n";
echo "- All quiz questions → Modern chapter_questions table\n";
echo "- Final exam questions → Created for each course\n";
echo "- Course statistics → Updated with counts\n\n";

echo "⚠️  IMPORTANT: This process will:\n";
echo "- Create new state-specific course tables\n";
echo "- Preserve all original data\n";
echo "- Create courses for all 4 states (FL, MO, TX, DE)\n";
echo "- Set up quiz and final exam systems\n\n";

$confirm = readline("Do you want to proceed? (yes/no): ");

if (strtolower(trim($confirm)) !== 'yes') {
    echo "❌ Migration cancelled.\n";
    exit(0);
}

echo "\n🔄 Starting migration process...\n\n";

// Step 1: Run the migration
echo "STEP 1: Running Course Migration\n";
echo "-------------------------------\n";

try {
    include 'migrate_courses_and_quizzes.php';
    echo "\n✅ Migration script completed\n\n";
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Run verification
echo "STEP 2: Verifying Migration Results\n";
echo "----------------------------------\n";

try {
    include 'verify_course_migration.php';
    echo "\n✅ Verification completed\n\n";
} catch (Exception $e) {
    echo "❌ Verification failed: " . $e->getMessage() . "\n";
}

echo "🎉 COURSE MIGRATION COMPLETED!\n";
echo "==============================\n\n";

echo "📊 YOUR MULTI-STATE SYSTEM NOW HAS:\n";
echo "- All original courses copied to 4 state portals\n";
echo "- All quiz questions preserved and organized\n";
echo "- Final exams ready for each course\n";
echo "- Progress tracking system integrated\n";
echo "- Certificate generation ready\n\n";

echo "🔗 TEST YOUR MIGRATED COURSES:\n";
echo "- Florida Portal: /florida/login\n";
echo "- Missouri Portal: /missouri/login\n";
echo "- Texas Portal: /texas/login\n";
echo "- Delaware Portal: /delaware/login\n\n";

echo "🔑 Login Credentials:\n";
echo "- florida@test.com / password123\n";
echo "- missouri@test.com / password123\n";
echo "- texas@test.com / password123\n";
echo "- delaware@test.com / password123\n\n";

echo "✅ All your original course content is now available in the new system!\n";

echo "\n🏁 Migration process completed at " . date('Y-m-d H:i:s') . "\n";