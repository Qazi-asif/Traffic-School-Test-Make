<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$enrollmentId = $argv[1] ?? 1;

$enrollment = App\Models\UserCourseEnrollment::findOrFail($enrollmentId);
$enrollment->status = 'completed';
$enrollment->completed_at = now();
$enrollment->progress_percentage = 100;
$enrollment->save();

echo "✓ Enrollment #{$enrollmentId} marked as completed!\n";
echo "✓ Observer triggered - certificate should be auto-generated\n";
echo "\nCheck certificates: http://127.0.0.1:8000/admin/certificates\n";
