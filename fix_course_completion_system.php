<?php
/**
 * Complete Course Completion System Fix
 * Fixes all issues with course progress, final exam completion, and certificate generation
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UserCourseEnrollment;
use App\Models\FinalExamResult;
use App\Models\FloridaCertificate;

echo "🚀 COMPLETE COURSE COMPLETION SYSTEM FIX\n";
echo "========================================\n\n";

try {
    // STEP 1: Analyze Current Issues
    echo "STEP 1: Analyzing Current Course Completion Issues\n";
    echo "-------------------------------------------------\n";
    
    // Check enrollments with final exa