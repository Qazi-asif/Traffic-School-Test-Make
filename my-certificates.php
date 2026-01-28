<?php
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
                                                <a href="/certificate/view?enrollment_id=<?= $cert->enrollment_id ?>" 
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
</html>