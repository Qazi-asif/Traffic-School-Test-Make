<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 FIX MY CERTIFICATES INTEGRATION\n";
echo "==================================\n\n";

try {
    // STEP 1: Check existing My Certificates route/controller
    echo "STEP 1: Checking Existing My Certificates System\n";
    echo "------------------------------------------------\n";
    
    // Check if there's already a my-certificates route
    $routesContent = file_get_contents('routes/web.php');
    $hasMyCertificatesRoute = strpos($routesContent, 'my-certificates') !== false;
    
    echo "✅ Existing my-certificates route: " . ($hasMyCertificatesRoute ? 'EXISTS' : 'MISSING') . "\n";
    
    // Check for existing certificate-related controllers
    $existingControllers = [];
    $controllerFiles = glob('app/Http/Controllers/*Certificate*.php');
    foreach ($controllerFiles as $file) {
        $existingControllers[] = basename($file);
    }
    
    echo "✅ Existing certificate controllers: " . (count($existingControllers) > 0 ? implode(', ', $existingControllers) : 'None') . "\n";
    
    // STEP 2: Create/Update the main certificate route and controller
    echo "\nSTEP 2: Creating My Certificates Route and Controller\n";
    echo "----------------------------------------------------\n";
    
    // Create a comprehensive certificate controller
    $certificateControllerPath = 'app/Http/Controllers/MyCertificatesController.php';
    $certificateControllerContent = '<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MyCertificatesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                if ($request->wantsJson()) {
                    return response()->json(["error" => "Not authenticated"], 401);
                }
                return redirect("/login");
            }
            
            \Log::info("Loading certificates for user", ["user_id" => $user->id, "email" => $user->email]);
            
            // Get all certificates for this user
            $certificates = DB::table("user_course_enrollments as uce")
                ->leftJoin("florida_courses as fc", function($join) {
                    $join->on("uce.course_id", "=", "fc.id")
                         ->where("uce.course_table", "=", "florida_courses");
                })
                ->leftJoin("courses as c", function($join) {
                    $join->on("uce.course_id", "=", "c.id")
                         ->where("uce.course_table", "=", "courses");
                })
                ->where("uce.user_id", $user->id)
                ->where("uce.status", "completed")
                ->whereNotNull("uce.certificate_generated_at")
                ->select([
                    "uce.id as enrollment_id",
                    "uce.certificate_number",
                    "uce.certificate_path",
                    "uce.certificate_generated_at",
                    "uce.completed_at",
                    "uce.course_id",
                    "uce.course_table",
                    DB::raw("COALESCE(fc.title, c.title, \"Traffic School Course\") as course_title"),
                    DB::raw("COALESCE(fc.state_code, c.state_code, c.state, \"FL\") as state_code")
                ])
                ->orderBy("uce.certificate_generated_at", "desc")
                ->get();

            \Log::info("Certificates loaded", ["user_id" => $user->id, "certificate_count" => $certificates->count()]);

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "certificates" => $certificates,
                    "count" => $certificates->count(),
                    "user" => [
                        "id" => $user->id,
                        "name" => $user->first_name . " " . $user->last_name,
                        "email" => $user->email
                    ]
                ]);
            }

            return view("student.my-certificates", [
                "certificates" => $certificates,
                "user" => $user
            ]);
            
        } catch (\Exception $e) {
            \Log::error("My certificates error: " . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    "success" => false,
                    "error" => "Failed to load certificates",
                    "message" => $e->getMessage(),
                    "certificates" => []
                ]);
            }
            
            return back()->with("error", "Failed to load certificates");
        }
    }

    public function download($enrollmentId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = DB::table("user_course_enrollments")
                ->where("id", $enrollmentId)
                ->where("user_id", $user->id)
                ->where("status", "completed")
                ->whereNotNull("certificate_generated_at")
                ->first();

            if (!$enrollment) {
                return response()->json(["error" => "Certificate not found"], 404);
            }

            $certificatePath = public_path($enrollment->certificate_path);
            
            if (!file_exists($certificatePath)) {
                return response()->json(["error" => "Certificate file not found"], 404);
            }

            return response()->download($certificatePath, "certificate-{$enrollment->certificate_number}.html");
            
        } catch (\Exception $e) {
            \Log::error("Certificate download error: " . $e->getMessage());
            return response()->json(["error" => "Download failed"], 500);
        }
    }
}';
    
    file_put_contents($certificateControllerPath, $certificateControllerContent);
    echo "✅ Created MyCertificatesController\n";
    
    // STEP 3: Add the routes
    echo "\nSTEP 3: Adding My Certificates Routes\n";
    echo "------------------------------------\n";
    
    $newRoutes = '
// My Certificates Routes - FIXED
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/my-certificates\', [App\Http\Controllers\MyCertificatesController::class, \'index\'])->name(\'my.certificates\');
    Route::get(\'/student/my-certificates\', [App\Http\Controllers\MyCertificatesController::class, \'index\'])->name(\'student.my.certificates\');
    Route::get(\'/api/my-certificates\', [App\Http\Controllers\MyCertificatesController::class, \'index\']);
    Route::get(\'/my-certificates/{enrollmentId}/download\', [App\Http\Controllers\MyCertificatesController::class, \'download\'])->name(\'my.certificates.download\');
});
';

    if (strpos($routesContent, 'my.certificates') === false) {
        $routesContent .= $newRoutes;
        file_put_contents('routes/web.php', $routesContent);
        echo "✅ My Certificates routes added\n";
    } else {
        echo "✅ My Certificates routes already exist\n";
    }
    
    // STEP 4: Create the student view
    echo "\nSTEP 4: Creating Student My Certificates View\n";
    echo "--------------------------------------------\n";
    
    $studentViewDir = 'resources/views/student';
    if (!file_exists($studentViewDir)) {
        mkdir($studentViewDir, 0755, true);
    }
    
    $myCertificatesView = $studentViewDir . '/my-certificates.blade.php';
    $viewContent = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-certificate"></i> 
                            My Certificates
                        </h3>
                        <small>{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</small>
                    </div>
                    <div class="card-body">
                        @if($certificates->count() > 0)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                You have {{ $certificates->count() }} certificate(s) available!
                            </div>
                            
                            @foreach($certificates as $cert)
                                <div class="card mb-3 border-success">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="card-title text-success">
                                                    <i class="fas fa-award"></i> {{ $cert->course_title }}
                                                </h5>
                                                <p class="card-text">
                                                    <strong>Certificate Number:</strong> 
                                                    <code>{{ $cert->certificate_number }}</code><br>
                                                    <strong>Completed:</strong> 
                                                    {{ date("F j, Y", strtotime($cert->certificate_generated_at)) }}<br>
                                                    <strong>State:</strong> {{ $cert->state_code }}<br>
                                                    <strong>Enrollment ID:</strong> {{ $cert->enrollment_id }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <a href="/view-certificate.php?id={{ $cert->enrollment_id }}" 
                                                   class="btn btn-primary btn-lg mb-2" target="_blank">
                                                    <i class="fas fa-eye"></i> View Certificate
                                                </a><br>
                                                <a href="/{{ $cert->certificate_path }}" 
                                                   class="btn btn-success" target="_blank" download>
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h4>No Certificates Yet</h4>
                                <p>Complete a course to earn your first certificate!</p>
                                <a href="/courses" class="btn btn-primary">
                                    <i class="fas fa-book"></i> Browse Courses
                                </a>
                            </div>
                        @endif
                        
                        <div class="mt-4 pt-3 border-top">
                            <h6>Debug Information:</h6>
                            <small class="text-muted">
                                User ID: {{ $user->id }} | 
                                Certificates Found: {{ $certificates->count() }} |
                                Generated at: {{ date("Y-m-d H:i:s") }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    
    file_put_contents($myCertificatesView, $viewContent);
    echo "✅ Created student my-certificates view\n";
    
    // STEP 5: Test the API endpoint
    echo "\nSTEP 5: Creating API Test for Chloe\n";
    echo "-----------------------------------\n";
    
    $apiTestContent = '<?php
// API Test for My Certificates
// Usage: /test-my-certificates-api.php?user_id=93

require_once "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

if (!isset($_GET["user_id"])) {
    echo "Usage: /test-my-certificates-api.php?user_id=93";
    exit;
}

$userId = (int)$_GET["user_id"];

// Get user
$user = DB::table("users")->where("id", $userId)->first();
if (!$user) {
    echo "User not found";
    exit;
}

echo "<h1>API Test for {$user->first_name} {$user->last_name}</h1>";
echo "<p>User ID: {$user->id} | Email: {$user->email}</p>";

// Test the exact query from the controller
try {
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
            "uce.course_id",
            "uce.course_table",
            DB::raw("COALESCE(fc.title, c.title, \"Traffic School Course\") as course_title"),
            DB::raw("COALESCE(fc.state_code, c.state_code, c.state, \"FL\") as state_code")
        ])
        ->orderBy("uce.certificate_generated_at", "desc")
        ->get();
    
    echo "<h2>API Query Result: {$certificates->count()} certificates</h2>";
    
    if ($certificates->count() > 0) {
        echo "<table border=\"1\" style=\"width:100%; border-collapse: collapse;\">";
        echo "<tr><th>Enrollment ID</th><th>Certificate #</th><th>Course Title</th><th>State</th><th>Generated</th><th>File Exists</th><th>Actions</th></tr>";
        
        foreach ($certificates as $cert) {
            $fileExists = file_exists(public_path($cert->certificate_path));
            echo "<tr>";
            echo "<td>{$cert->enrollment_id}</td>";
            echo "<td>{$cert->certificate_number}</td>";
            echo "<td>{$cert->course_title}</td>";
            echo "<td>{$cert->state_code}</td>";
            echo "<td>" . date("M j, Y", strtotime($cert->certificate_generated_at)) . "</td>";
            echo "<td>" . ($fileExists ? "✅ YES" : "❌ NO") . "</td>";
            echo "<td>";
            echo "<a href=\"/view-certificate.php?id={$cert->enrollment_id}\" target=\"_blank\">View</a> | ";
            echo "<a href=\"/{$cert->certificate_path}\" target=\"_blank\">Download</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>JSON Response:</h3>";
        echo "<pre>" . json_encode($certificates, JSON_PRETTY_PRINT) . "</pre>";
        
    } else {
        echo "<p>No certificates found in API query.</p>";
        
        // Debug: Check raw enrollment data
        $rawEnrollments = DB::table("user_course_enrollments")
            ->where("user_id", $userId)
            ->get();
        
        echo "<h3>Debug: All Enrollments for User</h3>";
        echo "<table border=\"1\">";
        echo "<tr><th>ID</th><th>Status</th><th>Course ID</th><th>Course Table</th><th>Cert Generated</th><th>Cert Number</th></tr>";
        
        foreach ($rawEnrollments as $enrollment) {
            echo "<tr>";
            echo "<td>{$enrollment->id}</td>";
            echo "<td>{$enrollment->status}</td>";
            echo "<td>{$enrollment->course_id}</td>";
            echo "<td>{$enrollment->course_table}</td>";
            echo "<td>" . ($enrollment->certificate_generated_at ?: "No") . "</td>";
            echo "<td>" . ($enrollment->certificate_number ?: "None") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<h2>API Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>';
    
    file_put_contents('test-my-certificates-api.php', $apiTestContent);
    echo "✅ Created API test page: /test-my-certificates-api.php\n";
    
    // STEP 6: Add routes to web.php
    echo "\nSTEP 6: Adding Routes to Web.php\n";
    echo "--------------------------------\n";
    
    $routesToAdd = '
// My Certificates Routes - COMPLETE FIX
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/my-certificates\', [App\Http\Controllers\MyCertificatesController::class, \'index\'])->name(\'my.certificates\');
    Route::get(\'/student/my-certificates\', [App\Http\Controllers\MyCertificatesController::class, \'index\'])->name(\'student.my.certificates\');
    Route::get(\'/api/my-certificates\', [App\Http\Controllers\MyCertificatesController::class, \'index\']);
    Route::get(\'/my-certificates/{enrollmentId}/download\', [App\Http\Controllers\MyCertificatesController::class, \'download\'])->name(\'my.certificates.download\');
});
';

    $currentRoutes = file_get_contents('routes/web.php');
    if (strpos($currentRoutes, 'MyCertificatesController') === false) {
        file_put_contents('routes/web.php', $currentRoutes . $routesToAdd);
        echo "✅ Routes added to web.php\n";
    } else {
        echo "✅ Routes already exist in web.php\n";
    }
    
    echo "\n🎉 MY CERTIFICATES INTEGRATION FIX COMPLETE!\n";
    echo "===========================================\n";
    echo "✅ MyCertificatesController created\n";
    echo "✅ Student view created\n";
    echo "✅ Routes added\n";
    echo "✅ API test page created\n";
    echo "✅ System ready for testing\n\n";
    
    echo "📋 CHLOE'S CERTIFICATE ACCESS (User ID: 93):\n";
    echo "1. Main page: /my-certificates\n";
    echo "2. Student page: /student/my-certificates\n";
    echo "3. API test: /test-my-certificates-api.php?user_id=93\n";
    echo "4. Direct certificate: /view-certificate.php?id=126\n";
    echo "5. Download: /certificates/cert-126.html\n\n";
    
    echo "🔧 INTEGRATION POINTS:\n";
    echo "- Add link to /my-certificates in your student navigation\n";
    echo "- After course completion, redirect to /my-certificates\n";
    echo "- Generate certificate button can link to /my-certificates\n\n";
    
    echo "🧪 TESTING STEPS:\n";
    echo "1. Login as Chloe (User ID: 93)\n";
    echo "2. Visit /my-certificates\n";
    echo "3. Should see her certificate: CERT-2026-000126\n";
    echo "4. Click View/Download buttons to access certificate\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";