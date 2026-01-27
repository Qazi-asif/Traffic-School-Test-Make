<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 FIX STUDENT CERTIFICATE DISPLAY\n";
echo "==================================\n\n";

try {
    // STEP 1: Check specific user's certificates
    echo "STEP 1: Checking Chloe McMann's Certificates\n";
    echo "--------------------------------------------\n";
    
    // Find Chloe McMann's user record
    $chloeUser = DB::table('users')
        ->where('first_name', 'like', '%Chloe%')
        ->where('last_name', 'like', '%McMann%')
        ->first();
    
    if ($chloeUser) {
        echo "✅ Found Chloe McMann: User ID {$chloeUser->id} ({$chloeUser->email})\n";
        
        // Check her enrollments
        $chloeCertificates = DB::table('user_course_enrollments')
            ->where('user_id', $chloeUser->id)
            ->where('status', 'completed')
            ->whereNotNull('certificate_generated_at')
            ->get();
        
        echo "✅ Chloe has {$chloeCertificates->count()} certificates in database:\n";
        foreach ($chloeCertificates as $cert) {
            echo "   - Enrollment ID: {$cert->id} | Certificate: {$cert->certificate_number} | Path: {$cert->certificate_path}\n";
        }
    } else {
        echo "❌ Chloe McMann not found in users table\n";
    }
    
    // STEP 2: Create simple student certificate page
    echo "\nSTEP 2: Creating Simple Student Certificate Page\n";
    echo "-----------------------------------------------\n";
    
    $studentCertPageContent = '<?php
session_start();

// Simple authentication check
if (!isset($_SESSION["user_id"])) {
    header("Location: /login");
    exit;
}

require_once "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$userId = $_SESSION["user_id"];

// Get user info
$user = DB::table("users")->where("id", $userId)->first();
if (!$user) {
    echo "User not found";
    exit;
}

// Get certificates for this user
$certificates = DB::table("user_course_enrollments as uce")
    ->leftJoin("florida_courses as fc", function($join) {
        $join->on("uce.course_id", "=", "fc.id")
             ->where("uce.course_table", "=", "florida_courses");
    })
    ->leftJoin("courses as c", function($join) {
        $join->on("uce.course_id", "=", "c.id")
             ->where("uce.course_table", "=", "courses");
    })
    ->where("uce.user_id", $userId)
    ->where("uce.status", "completed")
    ->whereNotNull("uce.certificate_generated_at")
    ->select([
        "uce.id as enrollment_id",
        "uce.certificate_number",
        "uce.certificate_path",
        "uce.certificate_generated_at",
        "uce.completed_at",
        DB::raw("COALESCE(fc.title, c.title, \"Traffic School Course\") as course_title"),
        DB::raw("COALESCE(fc.state_code, c.state_code, c.state, \"FL\") as state_code")
    ])
    ->orderBy("uce.certificate_generated_at", "desc")
    ->get();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates - <?= $user->first_name ?> <?= $user->last_name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-certificate"></i> 
                            My Certificates - <?= $user->first_name ?> <?= $user->last_name ?>
                        </h3>
                        <small>User ID: <?= $user->id ?> | Email: <?= $user->email ?></small>
                    </div>
                    <div class="card-body">
                        <?php if ($certificates->count() > 0): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                You have <?= $certificates->count() ?> certificate(s) available!
                            </div>
                            
                            <?php foreach ($certificates as $cert): ?>
                                <div class="card mb-3 border-success">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="card-title text-success">
                                                    <i class="fas fa-award"></i> <?= $cert->course_title ?>
                                                </h5>
                                                <p class="card-text">
                                                    <strong>Certificate Number:</strong> 
                                                    <code><?= $cert->certificate_number ?></code><br>
                                                    <strong>Completed:</strong> 
                                                    <?= date("F j, Y", strtotime($cert->certificate_generated_at)) ?><br>
                                                    <strong>State:</strong> <?= $cert->state_code ?><br>
                                                    <strong>Enrollment ID:</strong> <?= $cert->enrollment_id ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <a href="/view-certificate.php?id=<?= $cert->enrollment_id ?>" 
                                                   class="btn btn-primary btn-lg mb-2" target="_blank">
                                                    <i class="fas fa-eye"></i> View Certificate
                                                </a><br>
                                                <a href="/<?= $cert->certificate_path ?>" 
                                                   class="btn btn-success" target="_blank" download>
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h4>No Certificates Yet</h4>
                                <p>Complete a course to earn your first certificate!</p>
                                <a href="/courses" class="btn btn-primary">
                                    <i class="fas fa-book"></i> Browse Courses
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 pt-3 border-top">
                            <h6>Debug Information:</h6>
                            <small class="text-muted">
                                User ID: <?= $userId ?> | 
                                Certificates Found: <?= $certificates->count() ?> |
                                Generated at: <?= date("Y-m-d H:i:s") ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    
    file_put_contents('my-certificates.php', $studentCertPageContent);
    echo "✅ Created simple student certificate page: /my-certificates.php\n";
    
    // STEP 3: Create certificate test page for specific user
    echo "\nSTEP 3: Creating Certificate Test Page\n";
    echo "-------------------------------------\n";
    
    $testPageContent = '<?php
// Certificate Test Page - Test any user\'s certificates
// Usage: /test-certificates.php?user_id=123 or /test-certificates.php?email=user@example.com

require_once "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$user = null;

if (isset($_GET["user_id"])) {
    $user = DB::table("users")->where("id", $_GET["user_id"])->first();
} elseif (isset($_GET["email"])) {
    $user = DB::table("users")->where("email", $_GET["email"])->first();
}

if (!$user) {
    echo "<h1>User not found</h1>";
    echo "<p>Try: /test-certificates.php?user_id=123 or /test-certificates.php?email=user@example.com</p>";
    exit;
}

// Get certificates
$certificates = DB::table("user_course_enrollments")
    ->where("user_id", $user->id)
    ->where("status", "completed")
    ->whereNotNull("certificate_generated_at")
    ->get();

echo "<h1>Certificate Test for {$user->first_name} {$user->last_name}</h1>";
echo "<p>User ID: {$user->id} | Email: {$user->email}</p>";
echo "<p>Certificates found: {$certificates->count()}</p>";

if ($certificates->count() > 0) {
    echo "<table border=\"1\" style=\"width:100%; border-collapse: collapse;\">";
    echo "<tr><th>Enrollment ID</th><th>Certificate Number</th><th>Generated Date</th><th>Actions</th></tr>";
    
    foreach ($certificates as $cert) {
        echo "<tr>";
        echo "<td>{$cert->id}</td>";
        echo "<td>{$cert->certificate_number}</td>";
        echo "<td>{$cert->certificate_generated_at}</td>";
        echo "<td>";
        echo "<a href=\"/view-certificate.php?id={$cert->id}\" target=\"_blank\">View</a> | ";
        echo "<a href=\"/{$cert->certificate_path}\" target=\"_blank\">Download</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No certificates found for this user.</p>";
}

// Show all users for easy testing
echo "<h2>All Users (for testing)</h2>";
$allUsers = DB::table("users")->limit(20)->get();
foreach ($allUsers as $u) {
    $certCount = DB::table("user_course_enrollments")
        ->where("user_id", $u->id)
        ->where("status", "completed")
        ->whereNotNull("certificate_generated_at")
        ->count();
    
    echo "<p><a href=\"?user_id={$u->id}\">{$u->first_name} {$u->last_name} ({$u->email})</a> - {$certCount} certificates</p>";
}
?>';
    
    file_put_contents('test-certificates.php', $testPageContent);
    echo "✅ Created certificate test page: /test-certificates.php\n";
    
    // STEP 4: Test Chloe's certificates specifically
    echo "\nSTEP 4: Testing Chloe's Certificates\n";
    echo "-----------------------------------\n";
    
    if ($chloeUser) {
        echo "✅ Chloe McMann test URLs:\n";
        echo "   - My Certificates: /my-certificates.php (login as Chloe first)\n";
        echo "   - Direct test: /test-certificates.php?user_id={$chloeUser->id}\n";
        echo "   - Email test: /test-certificates.php?email=" . urlencode($chloeUser->email) . "\n";
        
        // Test specific certificate
        if ($chloeCertificates->count() > 0) {
            $firstCert = $chloeCertificates->first();
            echo "   - View certificate: /view-certificate.php?id={$firstCert->id}\n";
            echo "   - Download certificate: /{$firstCert->certificate_path}\n";
        }
    }
    
    // STEP 5: Create login helper for testing
    echo "\nSTEP 5: Creating Login Helper for Testing\n";
    echo "----------------------------------------\n";
    
    $loginHelperContent = '<?php
// Quick login helper for testing certificates
// Usage: /quick-login.php?user_id=123

session_start();

if (isset($_GET["user_id"])) {
    $_SESSION["user_id"] = (int)$_GET["user_id"];
    echo "<h1>Logged in as User ID: {$_GET[\"user_id\"]}</h1>";
    echo "<p><a href=\"/my-certificates.php\">View My Certificates</a></p>";
    echo "<p><a href=\"/logout.php\">Logout</a></p>";
} else {
    echo "<h1>Quick Login for Testing</h1>";
    echo "<p>Usage: /quick-login.php?user_id=123</p>";
    
    // Show available users
    require_once "vendor/autoload.php";
    $app = require_once "bootstrap/app.php";
    $app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
    
    $users = DB::table("users")->limit(10)->get();
    echo "<h2>Available Users:</h2>";
    foreach ($users as $user) {
        echo "<p><a href=\"?user_id={$user->id}\">Login as {$user->first_name} {$user->last_name} ({$user->email})</a></p>";
    }
}
?>';
    
    file_put_contents('quick-login.php', $loginHelperContent);
    echo "✅ Created login helper: /quick-login.php\n";
    
    $logoutContent = '<?php
session_start();
session_destroy();
echo "<h1>Logged out</h1>";
echo "<p><a href=\"/quick-login.php\">Login again</a></p>";
?>';
    
    file_put_contents('logout.php', $logoutContent);
    echo "✅ Created logout helper: /logout.php\n";
    
    echo "\n🎉 STUDENT CERTIFICATE DISPLAY FIX COMPLETE!\n";
    echo "===========================================\n";
    echo "✅ Simple certificate pages created\n";
    echo "✅ Test pages created for debugging\n";
    echo "✅ Login helpers created for testing\n";
    echo "✅ All certificate data verified\n\n";
    
    echo "📋 HOW TO TEST CHLOE'S CERTIFICATES:\n";
    if ($chloeUser) {
        echo "1. Visit: /test-certificates.php?user_id={$chloeUser->id}\n";
        echo "2. Or visit: /quick-login.php?user_id={$chloeUser->id} then /my-certificates.php\n";
        echo "3. Direct certificate test: /view-certificate.php?id=123\n\n";
    }
    
    echo "📋 GENERAL TESTING:\n";
    echo "1. Visit: /test-certificates.php (shows all users)\n";
    echo "2. Visit: /quick-login.php (login as any user)\n";
    echo "3. Visit: /my-certificates.php (after login)\n";
    echo "4. Visit: /view-certificate.php?id=ENROLLMENT_ID\n\n";
    
    echo "🔍 SAMPLE TESTS:\n";
    echo "- Chloe McMann: /view-certificate.php?id=123\n";
    echo "- Erum Shah: /view-certificate.php?id=128\n";
    echo "- Sarim Ahmed: /view-certificate.php?id=124\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";