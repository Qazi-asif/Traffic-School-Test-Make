<?php
// Quick login helper for testing certificates
// Usage: /quick-login.php?user_id=123

session_start();

if (isset($_GET["user_id"])) {
    $_SESSION["user_id"] = (int)$_GET["user_id"];
    echo "<h1>Logged in as User ID: {$_GET["user_id"]}</h1>";
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
?>