<?php
/**
 * Fix courses by setting state and enabling state transmission systems
 * Handles both courses and florida_courses tables
 * Usage: php fix-courses-state.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$stateMap = [
    'florida' => 'FL',
    'fl' => 'FL',
    'missouri' => 'Missouri',
    'mo' => 'Missouri',
    'texas' => 'TX',
    'tx' => 'TX',
    'delaware' => 'DE',
    'de' => 'DE',
    'california' => 'CA',
    'ca' => 'CA',
    'nevada' => 'NV',
    'nv' => 'NV',
];

echo "Fixing courses state and transmission settings...\n\n";

$totalUpdated = 0;
$totalSkipped = 0;

// Fix courses table
echo "=== Processing courses table ===\n";
$courses = DB::table('courses')->get();
$updated = 0;
$skipped = 0;

foreach ($courses as $course) {
    $titleLower = strtolower($course->title);
    $state = null;

    foreach ($stateMap as $keyword => $stateCode) {
        if (stripos($titleLower, $keyword) !== false) {
            $state = $stateCode;
            break;
        }
    }

    if ($course->state && $state === null) {
        echo "✓ Skipped ID {$course->id}: {$course->title} (state already set: {$course->state})\n";
        $skipped++;
        continue;
    }

    if ($state) {
        DB::table('courses')->where('id', $course->id)->update([
            'state' => $state,
            'tvcc_enabled' => $state === 'CA' ? 1 : 0,
            'ctsi_enabled' => $state === 'CA' ? 1 : 0,
            'ntsa_enabled' => $state === 'NV' ? 1 : 0,
            'ccs_enabled' => !in_array($state, ['FL', 'CA', 'NV']) ? 1 : 0,
        ]);
        echo "✓ Updated ID {$course->id}: {$course->title} → State: {$state}\n";
        $updated++;
    } else {
        echo "⚠ Skipped ID {$course->id}: {$course->title} (could not detect state)\n";
        $skipped++;
    }
}

echo "Courses table: Updated {$updated}, Skipped {$skipped}\n\n";
$totalUpdated += $updated;
$totalSkipped += $skipped;

// Fix florida_courses table
echo "=== Processing florida_courses table ===\n";
$floridaCourses = DB::table('florida_courses')->get();
$updated = 0;
$skipped = 0;

foreach ($floridaCourses as $course) {
    $titleLower = strtolower($course->title);
    $state = 'FL';

    if ($course->state_code && $course->state_code !== 'FL') {
        echo "✓ Skipped ID {$course->id}: {$course->title} (state_code already set: {$course->state_code})\n";
        $skipped++;
        continue;
    }

    DB::table('florida_courses')->where('id', $course->id)->update([
        'state_code' => $state,
    ]);
    echo "✓ Updated ID {$course->id}: {$course->title} → State: {$state}\n";
    $updated++;
}

echo "Florida courses table: Updated {$updated}, Skipped {$skipped}\n\n";
$totalUpdated += $updated;
$totalSkipped += $skipped;

// Check for other state-specific tables
$tables = ['nevada_courses', 'missouri_course_structures'];
foreach ($tables as $table) {
    if (DB::connection()->getSchemaBuilder()->hasTable($table)) {
        echo "=== Processing {$table} table ===\n";
        $stateCourses = DB::table($table)->get();
        $updated = 0;
        $skipped = 0;

        foreach ($stateCourses as $course) {
            $stateCode = match($table) {
                'nevada_courses' => 'NV',
                'missouri_course_structures' => 'Missouri',
                default => null,
            };

            if (!$stateCode) continue;

            $stateColumn = $table === 'florida_courses' ? 'state_code' : 'state';
            
            if (isset($course->$stateColumn) && $course->$stateColumn) {
                $skipped++;
                continue;
            }

            DB::table($table)->where('id', $course->id)->update([
                $stateColumn => $stateCode,
            ]);
            echo "✓ Updated ID {$course->id}\n";
            $updated++;
        }

        echo "{$table}: Updated {$updated}, Skipped {$skipped}\n\n";
        $totalUpdated += $updated;
        $totalSkipped += $skipped;
    }
}

echo "=== Final Summary ===\n";
echo "Total Updated: {$totalUpdated}\n";
echo "Total Skipped: {$totalSkipped}\n";
echo "Total Processed: " . ($totalUpdated + $totalSkipped) . "\n";
