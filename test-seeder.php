<?php

// Simple test script to verify our StateDataSeeder works
require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🌟 Testing State Data Seeder...\n";

try {
    // Run the seeder
    $seeder = new Database\Seeders\StateDataSeeder();
    $seeder->run();
    
    echo "✅ Seeder completed successfully!\n";
    
    // Verify data
    echo "\n📊 Verifying data:\n";
    echo "Florida courses: " . DB::table('florida_courses')->count() . "\n";
    echo "Missouri courses: " . DB::table('missouri_courses')->count() . "\n";
    echo "Texas courses: " . DB::table('texas_courses')->count() . "\n";
    echo "Delaware courses: " . DB::table('delaware_courses')->count() . "\n";
    
    echo "\nChapters created:\n";
    echo "Florida chapters: " . DB::table('florida_chapters')->count() . "\n";
    echo "Missouri chapters: " . DB::table('missouri_chapters')->count() . "\n";
    echo "Texas chapters: " . DB::table('texas_chapters')->count() . "\n";
    echo "Delaware chapters: " . DB::table('delaware_chapters')->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}