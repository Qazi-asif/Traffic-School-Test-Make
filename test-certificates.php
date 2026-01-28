<?php
// Certificate Test Page - Test any user's certificates
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
        echo "<a href=\"/certificate/view?enrollment_id={$cert->id}\" target=\"_blank\">View</a> | ";
        echo "<a href=\"/{$cert->certificate_path}\" target=\"_blank\">Download</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No certificates found for this user.</p>";
    
    // Check if user has completed enrollments without certificates
    $completedWithoutCerts = DB::table("user_course_enrollments")
        ->where("user_id", $user->id)
        ->where("status", "completed")
        ->whereNull("certificate_generated_at")
        ->count();
    
    if ($completedWithoutCerts > 0) {
        echo "<p style=\"color: orange;\">Found {$completedWithoutCerts} completed enrollments without certificates. <a href=\"/generate_certificates.php\">Generate certificates</a></p>";
    }
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
?>