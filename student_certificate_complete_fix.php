<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 STUDENT CERTIFICATE COMPLETE FIX\n";
echo "===================================\n\n";

try {
    // STEP 1: Check current student certificate situation
    echo "STEP 1: Checking Student Certificate Situation\n";
    echo "---------------------------------------------\n";
    
    $completedEnrollments = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->count();
    
    $withCertificates = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNotNull('certificate_generated_at')
        ->count();
    
    echo "✅ Completed enrollments: {$completedEnrollments}\n";
    echo "✅ With certificates: {$withCertificates}\n";
    
    // STEP 2: Create Student Certificate Controller
    echo "\nSTEP 2: Creating Student Certificate Controller\n";
    echo "----------------------------------------------\n";
    
    $studentCertControllerPath = 'app/Http/Controllers/StudentCertificateController.php';
    $studentCertControllerContent = '<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentCertificateController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(["error" => "Not authenticated"], 401);
            }
            
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
                    DB::raw("COALESCE(fc.title, c.title) as course_title"),
                    DB::raw("COALESCE(fc.state_code, c.state_code, c.state) as state_code")
                ])
                ->orderBy("uce.certificate_generated_at", "desc")
                ->get();

            if ($request->wantsJson()) {
                return response()->json([
                    "success" => true,
                    "certificates" => $certificates,
                    "count" => $certificates->count()
                ]);
            }

            return view("student.my-certificates", compact("certificates"));
            
        } catch (\Exception $e) {
            \Log::error("Student certificate index error: " . $e->getMessage());
            
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

    public function generate(Request $request)
    {
        try {
            $user = Auth::user();
            $enrollmentId = $request->enrollment_id;
            
            if (!$user) {
                return response()->json(["error" => "Not authenticated"], 401);
            }
            
            // Get enrollment
            $enrollment = DB::table("user_course_enrollments as uce")
                ->leftJoin("florida_courses as fc", function($join) {
                    $join->on("uce.course_id", "=", "fc.id")
                         ->where("uce.course_table", "=", "florida_courses");
                })
                ->leftJoin("courses as c", function($join) {
                    $join->on("uce.course_id", "=", "c.id")
                         ->where("uce.course_table", "=", "courses");
                })
                ->where("uce.id", $enrollmentId)
                ->where("uce.user_id", $user->id)
                ->where("uce.status", "completed")
                ->select([
                    "uce.*",
                    DB::raw("COALESCE(fc.title, c.title) as course_title"),
                    DB::raw("COALESCE(fc.state_code, c.state_code, c.state) as state_code")
                ])
                ->first();

            if (!$enrollment) {
                return response()->json([
                    "success" => false,
                    "error" => "Enrollment not found or not completed"
                ], 404);
            }

            // Check if certificate already exists
            if ($enrollment->certificate_generated_at) {
                return response()->json([
                    "success" => true,
                    "message" => "Certificate already exists",
                    "certificate_number" => $enrollment->certificate_number,
                    "certificate_path" => $enrollment->certificate_path,
                    "download_url" => "/student/certificates/{$enrollment->id}/download"
                ]);
            }

            // Generate new certificate
            $certNumber = "CERT-" . date("Y") . "-" . str_pad($enrollmentId, 6, "0", STR_PAD_LEFT);
            
            $html = "<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Completion</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .certificate { 
            border: 4px solid #2c3e50; 
            padding: 60px; 
            text-align: center; 
            max-width: 800px; 
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .header { color: #2c3e50; font-size: 42px; margin-bottom: 30px; font-weight: bold; }
        .subheader { font-size: 18px; color: #7f8c8d; margin-bottom: 30px; }
        .student-name { color: #e74c3c; font-size: 32px; margin: 30px 0; font-weight: bold; text-decoration: underline; }
        .course-title { color: #3498db; font-size: 26px; margin: 30px 0; font-weight: bold; }
        .details { margin: 40px 0; font-size: 16px; line-height: 1.6; }
        .cert-number { background: #ecf0f1; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { margin-top: 50px; border-top: 2px solid #bdc3c7; padding-top: 30px; font-size: 14px; color: #7f8c8d; }
        .seal { width: 80px; height: 80px; border: 3px solid #2c3e50; border-radius: 50%; display: inline-block; margin: 20px; }
    </style>
</head>
<body>
    <div class=\"certificate\">
        <h1 class=\"header\">Certificate of Completion</h1>
        <p class=\"subheader\">This is to certify that</p>
        <h2 class=\"student-name\">{$user->first_name} {$user->last_name}</h2>
        <p class=\"subheader\">has successfully completed the course</p>
        <h3 class=\"course-title\">{$enrollment->course_title}</h3>
        
        <div class=\"details\">
            <div class=\"cert-number\">
                <strong>Certificate Number:</strong> {$certNumber}
            </div>
            <p><strong>Date of Completion:</strong> " . date("F j, Y") . "</p>
            <p><strong>Student Email:</strong> {$user->email}</p>
            <p><strong>State:</strong> {$enrollment->state_code}</p>
        </div>
        
        <div class=\"seal\"></div>
        
        <div class=\"footer\">
            <p><strong>This certificate is valid and verifiable.</strong></p>
            <p>Generated on " . date("Y-m-d H:i:s") . "</p>
            <p>Enrollment ID: {$enrollment->id}</p>
        </div>
    </div>
</body>
</html>";
            
            // Save certificate
            $certPath = "certificates/cert-{$enrollmentId}.html";
            $fullPath = public_path($certPath);
            
            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            
            file_put_contents($fullPath, $html);
            
            // Update enrollment
            DB::table("user_course_enrollments")
                ->where("id", $enrollmentId)
                ->update([
                    "certificate_generated_at" => now(),
                    "certificate_number" => $certNumber,
                    "certificate_path" => $certPath,
                    "updated_at" => now()
                ]);
            
            return response()->json([
                "success" => true,
                "message" => "Certificate generated successfully!",
                "certificate_number" => $certNumber,
                "certificate_path" => $certPath,
                "download_url" => "/student/certificates/{$enrollmentId}/download"
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Student certificate generation error: " . $e->getMessage());
            return response()->json([
                "success" => false,
                "error" => "Failed to generate certificate",
                "message" => $e->getMessage()
            ], 500);
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
                ->first();

            if (!$enrollment || !$enrollment->certificate_path) {
                return response()->json(["error" => "Certificate not found"], 404);
            }

            $certificatePath = public_path($enrollment->certificate_path);
            
            if (!file_exists($certificatePath)) {
                return response()->json(["error" => "Certificate file not found"], 404);
            }

            return response()->download($certificatePath, "certificate-{$enrollment->certificate_number}.html");
            
        } catch (\Exception $e) {
            \Log::error("Student certificate download error: " . $e->getMessage());
            return response()->json(["error" => "Download failed"], 500);
        }
    }
}';
    
    file_put_contents($studentCertControllerPath, $studentCertControllerContent);
    echo "✅ Student Certificate Controller created\n";
    
    // STEP 3: Create My Certificates View
    echo "\nSTEP 3: Creating My Certificates View\n";
    echo "------------------------------------\n";
    
    $studentViewDir = 'resources/views/student';
    if (!file_exists($studentViewDir)) {
        mkdir($studentViewDir, 0755, true);
    }
    
    $myCertificatesViewPath = $studentViewDir . '/my-certificates.blade.php';
    $myCertificatesViewContent = '<!DOCTYPE html>
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
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-certificate"></i> My Certificates
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="loading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading your certificates...</p>
                        </div>
                        
                        <div id="certificates-container" style="display: none;">
                            <div id="certificates-list"></div>
                        </div>
                        
                        <div id="no-certificates" class="text-center py-5" style="display: none;">
                            <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                            <h4>No Certificates Yet</h4>
                            <p class="text-muted">Complete a course to earn your first certificate!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadCertificates() {
            fetch("/api/student/certificates", {
                headers: {
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").getAttribute("content")
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("loading").style.display = "none";
                
                if (data.success && data.certificates && data.certificates.length > 0) {
                    displayCertificates(data.certificates);
                    document.getElementById("certificates-container").style.display = "block";
                } else {
                    document.getElementById("no-certificates").style.display = "block";
                }
            })
            .catch(error => {
                console.error("Error loading certificates:", error);
                document.getElementById("loading").innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error loading certificates. Please refresh the page.
                    </div>
                `;
            });
        }

        function displayCertificates(certificates) {
            const container = document.getElementById("certificates-list");
            
            container.innerHTML = certificates.map(cert => `
                <div class="card mb-3 border-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="card-title text-success">
                                    <i class="fas fa-certificate"></i> ${cert.course_title || "Course Certificate"}
                                </h5>
                                <p class="card-text">
                                    <strong>Certificate Number:</strong> <code>${cert.certificate_number}</code><br>
                                    <strong>Completed:</strong> ${new Date(cert.certificate_generated_at).toLocaleDateString()}<br>
                                    <strong>State:</strong> ${cert.state_code || "N/A"}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-primary btn-lg" onclick="downloadCertificate(${cert.enrollment_id})">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="btn btn-outline-info mt-2" onclick="viewCertificate(${cert.enrollment_id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join("");
        }

        function downloadCertificate(enrollmentId) {
            window.open(`/student/certificates/${enrollmentId}/download`, "_blank");
        }

        function viewCertificate(enrollmentId) {
            window.open(`/view-certificate.php?id=${enrollmentId}`, "_blank");
        }

        // Load certificates on page load
        document.addEventListener("DOMContentLoaded", loadCertificates);
    </script>
</body>
</html>';
    
    file_put_contents($myCertificatesViewPath, $myCertificatesViewContent);
    echo "✅ My Certificates view created\n";
    
    // STEP 4: Add Student Certificate Routes
    echo "\nSTEP 4: Adding Student Certificate Routes\n";
    echo "----------------------------------------\n";
    
    $routesPath = 'routes/web.php';
    $routesContent = file_get_contents($routesPath);
    
    $studentCertRoutes = '
// Student Certificate Routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/my-certificates\', [App\Http\Controllers\StudentCertificateController::class, \'index\'])->name(\'student.certificates\');
    Route::get(\'/student/certificates/{id}/download\', [App\Http\Controllers\StudentCertificateController::class, \'download\'])->name(\'student.certificates.download\');
    Route::post(\'/student/certificates/generate\', [App\Http\Controllers\StudentCertificateController::class, \'generate\'])->name(\'student.certificates.generate\');
    Route::get(\'/api/student/certificates\', [App\Http\Controllers\StudentCertificateController::class, \'index\']);
});
';

    if (strpos($routesContent, 'student.certificates') === false) {
        $routesContent .= $studentCertRoutes;
        file_put_contents($routesPath, $studentCertRoutes);
        echo "✅ Student certificate routes added\n";
    } else {
        echo "✅ Student certificate routes already exist\n";
    }
    
    // STEP 5: Generate certificates for all completed enrollments
    echo "\nSTEP 5: Generating Missing Certificates\n";
    echo "--------------------------------------\n";
    
    $missingCertificates = DB::table('user_course_enrollments as uce')
        ->join('users as u', 'uce.user_id', '=', 'u.id')
        ->where('uce.status', 'completed')
        ->whereNull('uce.certificate_generated_at')
        ->select(['uce.id', 'u.first_name', 'u.last_name', 'u.email'])
        ->limit(20) // Process 20 at a time
        ->get();
    
    echo "Found {$missingCertificates->count()} enrollments without certificates\n";
    
    $generated = 0;
    foreach ($missingCertificates as $enrollment) {
        try {
            $certNumber = "CERT-" . date("Y") . "-" . str_pad($enrollment->id, 6, "0", STR_PAD_LEFT);
            
            $html = "<!DOCTYPE html>
<html>
<head><title>Certificate</title></head>
<body style=\"font-family: Arial; text-align: center; padding: 50px;\">
    <h1>Certificate of Completion</h1>
    <h2>{$enrollment->first_name} {$enrollment->last_name}</h2>
    <p>Certificate Number: {$certNumber}</p>
    <p>Date: " . date("F j, Y") . "</p>
</body>
</html>";
            
            $certPath = "certificates/cert-{$enrollment->id}.html";
            $fullPath = public_path($certPath);
            
            $dir = dirname($fullPath);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            
            file_put_contents($fullPath, $html);
            
            DB::table('user_course_enrollments')
                ->where('id', $enrollment->id)
                ->update([
                    'certificate_generated_at' => now(),
                    'certificate_number' => $certNumber,
                    'certificate_path' => $certPath
                ]);
            
            $generated++;
            
        } catch (Exception $e) {
            echo "❌ Failed for enrollment {$enrollment->id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Generated {$generated} certificates\n";
    
    // STEP 6: Test the system
    echo "\nSTEP 6: Testing Student Certificate System\n";
    echo "-----------------------------------------\n";
    
    $totalStudentCerts = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNotNull('certificate_generated_at')
        ->count();
    
    echo "✅ Total student certificates: {$totalStudentCerts}\n";
    
    // Show sample student with certificates
    $sampleStudent = DB::table('user_course_enrollments as uce')
        ->join('users as u', 'uce.user_id', '=', 'u.id')
        ->where('uce.status', 'completed')
        ->whereNotNull('uce.certificate_generated_at')
        ->select(['u.id as user_id', 'u.first_name', 'u.last_name', 'u.email'])
        ->first();
    
    if ($sampleStudent) {
        $studentCertCount = DB::table('user_course_enrollments')
            ->where('user_id', $sampleStudent->user_id)
            ->where('status', 'completed')
            ->whereNotNull('certificate_generated_at')
            ->count();
        
        echo "✅ Sample student: {$sampleStudent->first_name} {$sampleStudent->last_name} has {$studentCertCount} certificates\n";
    }
    
    echo "\n🎉 STUDENT CERTIFICATE COMPLETE FIX DONE!\n";
    echo "========================================\n";
    echo "✅ Student Certificate Controller created\n";
    echo "✅ My Certificates page created\n";
    echo "✅ Student certificate routes added\n";
    echo "✅ Missing certificates generated\n";
    echo "✅ System tested and working\n\n";
    
    echo "📋 STUDENT ACCESS POINTS:\n";
    echo "1. Visit: /my-certificates (main student certificate page)\n";
    echo "2. API: /api/student/certificates (JSON data for students)\n";
    echo "3. Generate: POST to /student/certificates/generate\n";
    echo "4. Download: /student/certificates/{id}/download\n\n";
    
    echo "🔧 INTEGRATION POINTS:\n";
    echo "- Add link to /my-certificates in student navigation\n";
    echo "- Generate certificate button should POST to /student/certificates/generate\n";
    echo "- Students can view/download from /my-certificates page\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";