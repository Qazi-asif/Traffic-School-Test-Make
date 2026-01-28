<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get certificates from enrollments with certificate data
            $query = DB::table("user_course_enrollments as uce")
                ->join("users as u", "uce.user_id", "=", "u.id")
                ->leftJoin("florida_courses as fc", function($join) {
                    $join->on("uce.course_id", "=", "fc.id")
                         ->where("uce.course_table", "=", "florida_courses");
                })
                ->leftJoin("courses as c", function($join) {
                    $join->on("uce.course_id", "=", "c.id")
                         ->where("uce.course_table", "=", "courses");
                })
                ->where("uce.status", "completed")
                ->whereNotNull("uce.certificate_generated_at")
                ->select([
                    "uce.id as enrollment_id",
                    "uce.certificate_number",
                    "uce.certificate_path", 
                    "uce.certificate_generated_at",
                    "uce.completed_at",
                    "u.first_name",
                    "u.last_name",
                    "u.email",
                    DB::raw("COALESCE(fc.title, c.title) as course_title"),
                    DB::raw("COALESCE(fc.state_code, c.state_code, c.state) as state_code"),
                    "uce.course_table"
                ]);

            // Apply filters
            if ($request->state) {
                $query->where(function($q) use ($request) {
                    $q->where("fc.state_code", $request->state)
                      ->orWhere("c.state_code", $request->state)
                      ->orWhere("c.state", $request->state);
                });
            }

            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where("u.first_name", "like", "%{$search}%")
                      ->orWhere("u.last_name", "like", "%{$search}%")
                      ->orWhere("u.email", "like", "%{$search}%")
                      ->orWhere("uce.certificate_number", "like", "%{$search}%");
                });
            }

            $certificates = $query->orderBy("uce.certificate_generated_at", "desc")
                                 ->paginate(50);

            if ($request->wantsJson()) {
                return response()->json($certificates);
            }

            return view("admin.certificates", compact("certificates"));
            
        } catch (\Exception $e) {
            \Log::error("Certificate index error: " . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    "error" => "Failed to load certificates",
                    "message" => $e->getMessage()
                ], 500);
            }
            
            return back()->with("error", "Failed to load certificates: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $enrollment = DB::table("user_course_enrollments as uce")
                ->join("users as u", "uce.user_id", "=", "u.id")
                ->leftJoin("florida_courses as fc", function($join) {
                    $join->on("uce.course_id", "=", "fc.id")
                         ->where("uce.course_table", "=", "florida_courses");
                })
                ->leftJoin("courses as c", function($join) {
                    $join->on("uce.course_id", "=", "c.id")
                         ->where("uce.course_table", "=", "courses");
                })
                ->where("uce.id", $id)
                ->select([
                    "uce.*",
                    "u.first_name",
                    "u.last_name", 
                    "u.email",
                    DB::raw("COALESCE(fc.title, c.title) as course_title"),
                    DB::raw("COALESCE(fc.state_code, c.state_code, c.state) as state_code")
                ])
                ->first();

            if (!$enrollment) {
                return response()->json(["error" => "Certificate not found"], 404);
            }

            return response()->json($enrollment);
            
        } catch (\Exception $e) {
            \Log::error("Certificate show error: " . $e->getMessage());
            return response()->json([
                "error" => "Failed to load certificate",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        try {
            $enrollment = DB::table("user_course_enrollments")
                ->where("id", $id)
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
            return response()->json([
                "error" => "Failed to download certificate",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function generate(Request $request)
    {
        try {
            $enrollmentId = $request->enrollment_id;
            
            $enrollment = DB::table("user_course_enrollments as uce")
                ->join("users as u", "uce.user_id", "=", "u.id")
                ->leftJoin("florida_courses as fc", function($join) {
                    $join->on("uce.course_id", "=", "fc.id")
                         ->where("uce.course_table", "=", "florida_courses");
                })
                ->leftJoin("courses as c", function($join) {
                    $join->on("uce.course_id", "=", "c.id")
                         ->where("uce.course_table", "=", "courses");
                })
                ->where("uce.id", $enrollmentId)
                ->where("uce.status", "completed")
                ->select([
                    "uce.*",
                    "u.first_name",
                    "u.last_name",
                    "u.email",
                    DB::raw("COALESCE(fc.title, c.title) as course_title"),
                    DB::raw("COALESCE(fc.state_code, c.state_code, c.state) as state_code")
                ])
                ->first();

            if (!$enrollment) {
                return response()->json(["error" => "Enrollment not found or not completed"], 404);
            }

            // Generate certificate if not already generated
            if (!$enrollment->certificate_generated_at) {
                $certNumber = "CERT-" . date("Y") . "-" . str_pad($enrollmentId, 6, "0", STR_PAD_LEFT);
                
                $html = view("certificate-pdf", [
                    "enrollment" => $enrollment,
                    "certificate_number" => $certNumber,
                    "generated_date" => date("F j, Y")
                ])->render();
                
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
                        "certificate_path" => $certPath
                    ]);
                
                return response()->json([
                    "success" => true,
                    "message" => "Certificate generated successfully",
                    "certificate_number" => $certNumber,
                    "certificate_path" => $certPath
                ]);
            } else {
                return response()->json([
                    "success" => true,
                    "message" => "Certificate already exists",
                    "certificate_number" => $enrollment->certificate_number,
                    "certificate_path" => $enrollment->certificate_path
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error("Certificate generation error: " . $e->getMessage());
            return response()->json([
                "error" => "Failed to generate certificate",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function view(Request $request)
    {
        try {
            $enrollmentId = $request->input("enrollment_id");
            
            if (!$enrollmentId) {
                return response()->json(["error" => "Enrollment ID required"], 400);
            }
            
            // Get enrollment data
            $enrollment = DB::table("user_course_enrollments as uce")
                ->leftJoin("users as u", "uce.user_id", "=", "u.id")
                ->leftJoin("florida_courses as fc", function($join) {
                    $join->on("uce.course_id", "=", "fc.id")
                         ->where("uce.course_table", "=", "florida_courses");
                })
                ->leftJoin("courses as c", function($join) {
                    $join->on("uce.course_id", "=", "c.id")
                         ->where("uce.course_table", "=", "courses");
                })
                ->where("uce.id", $enrollmentId)
                ->select([
                    "uce.*",
                    "u.first_name as student_first_name",
                    "u.last_name as student_last_name",
                    "u.email as student_email",
                    DB::raw("COALESCE(fc.title, c.title) as course_title"),
                    DB::raw("COALESCE(fc.description, c.description) as course_description"),
                    DB::raw("COALESCE(fc.state_code, c.state_code, c.state) as state_code")
                ])
                ->first();
            
            if (!$enrollment) {
                return response()->json(["error" => "Enrollment not found"], 404);
            }
            
            // Create certificate data
            $certificate = (object) [
                "id" => $enrollment->id,
                "student_name" => $enrollment->student_first_name . ' ' . $enrollment->student_last_name,
                "course_title" => $enrollment->course_title,
                "course_description" => $enrollment->course_description,
                "completion_date" => $enrollment->completed_at ?? $enrollment->updated_at,
                "certificate_number" => "CERT-" . date("Y") . "-" . str_pad($enrollment->id, 6, "0", STR_PAD_LEFT)
            ];
            
            $stateCode = strtoupper($enrollment->state_code ?? "FL");
            $sealUrl = "/images/state-stamps/{$stateCode}-seal.png";
            
            // Check if seal exists, fallback to default
            $sealPath = public_path("images/state-stamps/{$stateCode}-seal.png");
            if (!file_exists($sealPath)) {
                $sealUrl = "/images/state-stamps/FL-seal.png";
            }
            
            return view("certificate-pdf", compact("certificate", "stateCode", "sealUrl"));
            
        } catch (\Exception $e) {
            \Log::error("Certificate view error: " . $e->getMessage());
            return response()->json(["error" => "Failed to view certificate"], 500);
        }
    }
}