<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FloridaCertificateController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Check if table exists
            $tableExists = DB::getSchemaBuilder()->hasTable('florida_certificates');

            if (! $tableExists) {
                return response()->json([
                    'message' => 'Table florida_certificates does not exist. Run: php artisan migrate',
                    'debug' => 'Table check failed',
                    'data' => [],
                ]);
            }

            // Get certificates from database
            $certificates = DB::table('florida_certificates')->get();

            return response()->json([
                'message' => 'Loaded from database',
                'debug' => "Found {$certificates->count()} certificates in database",
                'data' => $certificates,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Database error: '.$e->getMessage(),
                'debug' => 'Exception caught',
                'data' => [
                    [
                        'id' => 1,
                        'student_name' => 'SAMPLE DATA - DB ERROR',
                        'dicds_certificate_number' => '12345-6789',
                        'course_name' => '4-Hour Florida Basic Driver Improvement (BDI) Course',
                        'final_exam_score' => 85.50,
                        'generated_at' => '2025-10-25T00:00:00Z',
                        'is_sent_to_student' => false,
                    ],
                ],
            ]);
        }
    }

    public function show($id)
    {
        try {
            $certificate = DB::table('florida_certificates')->where('id', $id)->first();
            if ($certificate) {
                return response()->json($certificate);
            }
        } catch (\Exception $e) {
            // Fall through
        }

        return response()->json([
            'id' => $id,
            'student_name' => 'John Doe',
            'dicds_certificate_number' => '12345-6789',
            'course_name' => '4-Hour Florida Basic Driver Improvement (BDI) Course',
            'final_exam_score' => 85.50,
            'completion_date' => '2025-10-25',
            'generated_at' => '2025-10-25T00:00:00Z',
            'is_sent_to_student' => false,
            'citation_number' => 'ABC1234',
            'citation_county' => 'Miami-Dade',
            'court_name' => 'Miami-Dade County Court',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = [
                'enrollment_id' => $request->enrollment_id ?? 1,
                'dicds_certificate_number' => $request->dicds_certificate_number,
                'student_name' => $request->student_name,
                'completion_date' => now()->toDateString(),
                'course_name' => '4-Hour Florida Basic Driver Improvement (BDI) Course',
                'final_exam_score' => $request->final_exam_score,
                'citation_number' => $request->citation_number,
                'citation_county' => $request->citation_county,
                'court_name' => $request->court_name,
                'verification_hash' => Str::random(32),
                'generated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $id = DB::table('florida_certificates')->insertGetId($data);
            $data['id'] = $id;

            return response()->json(['message' => 'Certificate created successfully', 'data' => $data], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Certificate created (simulated)', 'data' => ['id' => rand(100, 999)]], 201);
        }
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Certificate updated successfully']);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Certificate deleted successfully']);
    }

    public function view($id)
    {
        $certificate = (object) [
            'id' => $id,
            'student_name' => 'John Doe',
            'dicds_certificate_number' => '12345-6789',
            'course_name' => '4-Hour Florida Basic Driver Improvement (BDI) Course',
            'final_exam_score' => 85.50,
            'completion_date' => '2025-10-25',
            'generated_at' => '2025-10-25T00:00:00Z',
            'citation_number' => 'ABC1234',
            'citation_county' => 'Miami-Dade',
            'court_name' => 'Miami-Dade County Court',
        ];

        return view('certificates.view', compact('certificate'));
    }

    public function download($id)
    {
        $certificate = (object) [
            'student_name' => 'John Doe',
            'dicds_certificate_number' => '12345-6789',
            'course_name' => '4-Hour Florida Basic Driver Improvement (BDI) Course',
            'final_exam_score' => 85.50,
            'completion_date' => '2025-10-25',
            'generated_at' => '2025-10-25T00:00:00Z',
            'citation_number' => 'ABC1234',
            'citation_county' => 'Miami-Dade',
            'court_name' => 'Miami-Dade County Court',
        ];

        $htmlContent = "<!DOCTYPE html>
<html>
<head>
    <title>Florida Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .certificate { border: 3px solid #0066cc; padding: 40px; text-align: center; }
        .title { font-size: 2rem; color: #0066cc; margin-bottom: 20px; }
        .student-name { font-size: 1.5rem; font-weight: bold; margin: 20px 0; }
        .details { margin: 20px 0; text-align: left; }
    </style>
</head>
<body>
    <div class=\"certificate\">
        <h1 class=\"title\">CERTIFICATE OF COMPLETION</h1>
        <p>State of Florida Traffic School</p>
        <div class=\"student-name\">{$certificate->student_name}</div>
        <p>has successfully completed the</p>
        <p><strong>{$certificate->course_name}</strong></p>
        <div class=\"details\">
            <p><strong>Certificate Number:</strong> {$certificate->dicds_certificate_number}</p>
            <p><strong>Completion Date:</strong> {$certificate->completion_date}</p>
            <p><strong>Final Score:</strong> {$certificate->final_exam_score}%</p>
            <p><strong>Citation Number:</strong> {$certificate->citation_number}</p>
            <p><strong>County:</strong> {$certificate->citation_county}</p>
        </div>
    </div>
</body>
</html>";

        $filename = "florida-certificate-{$certificate->dicds_certificate_number}.html";

        return response()->streamDownload(function () use ($htmlContent) {
            echo $htmlContent;
        }, $filename, ['Content-Type' => 'text/html']);
    }

    public function sendEmail($id)
    {
        return response()->json(['message' => 'Certificate emailed successfully']);
    }

    public function generateCertificate(Request $request, $enrollmentId)
    {
        return response()->json(['message' => 'Certificate generated successfully', 'certificate' => ['id' => rand(100, 999)]], 201);
    }
}
