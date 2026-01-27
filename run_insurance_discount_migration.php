<?php
/**
 * Run Insurance Discount Migration
 * 
 * This script adds the insurance_discount_only column to the users table
 * Run this from command line: php run_insurance_discount_migration.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "=== Insurance Discount Migration ===\n\n";

try {
    // Check if column already exists
    $hasColumn = Schema::hasColumn('users', 'insurance_discount_only');
    
    if ($hasColumn) {
        echo "✓ Column 'insurance_discount_only' already exists in users table\n";
        exit(0);
    }
    
    echo "Adding 'insurance_discount_only' column to users table...\n";
    
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('insurance_discount_only')->default(false)->after('license_class');
    });
    
    echo "✓ Successfully added 'insurance_discount_only' column\n\n";
    
    // Verify the column was added
    $columns = DB::select("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME = 'insurance_discount_only'
    ");
    
    if (!empty($columns)) {
        echo "Column details:\n";
        foreach ($columns as $col) {
            echo "  - Name: {$col->COLUMN_NAME}\n";
            echo "  - Type: {$col->DATA_TYPE}\n";
            echo "  - Nullable: {$col->IS_NULLABLE}\n";
            echo "  - Default: {$col->COLUMN_DEFAULT}\n";
        }
    }
    
    echo "\n✓ Migration completed successfully!\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
