<?php

namespace App\Http\Controllers;

use App\Events\CertificateGenerated;
use App\Models\Certificate;
use App\Models\FloridaCertificate;
use App\Models\UserCourseEnrollment;
use App\Services\CertificateAccessService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = FloridaCertificate::query();

        if ($request->state_code) {
            $query->where('state', $request->state_code);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $certificates = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($certificates);
    }

    public function generate(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect('/login')->with('error', 'Please login to generate certificate');
        }

        // Use URL parameters if provided, otherwise fallback to database
        $enrollment = null;
        if ($request->enrollment_id) {
            $enrollment = UserCourseEnrollment::with(['course', 'user'])
                ->where('user_id', $user->id)
                ->where('id', $request->enrollment_id)
                ->first();

            // Check if review exists for this enrollment
            $review = \App\Models\Review::where('user_id', $user->id)
                ->where('enrollment_id', $request->enrollment_id)
                ->first();

            if (! $review) {
                // Redirect to review page if no review exists
                return redirect('/review-course?'.http_build_query([
                    'enrollment_id' => $request->enrollment_id,
                    'course_name' => $enrollment && $enrollment->course ? $enrollment->course->title : 'Course',
                    'completion_date' => $request->completion_date ?: ($enrollment && $enrollment->completed_at ? $enrollment->completed_at->format('m/d/Y') : date('m/d/Y')),
                    'score' => $request->score ?: '95%',
                ]));
            }
        }

        // Build student address
        $addressParts = array_filter([
            $user->mailing_address,
            $user->city,
            $user->state,
            $user->zip,
        ]);
        $student_address = implode(', ', $addressParts);

        // Build phone number
        $phone_parts = array_filter([$user->phone_1, $user->phone_2, $user->phone_3]);
        $phone = implode('-', $phone_parts);

        // Build birth date
        $birth_date = null;
        if ($user->birth_month && $user->birth_day && $user->birth_year) {
            $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
        }

        // Build due date
        $due_date = null;
        if ($user->due_month && $user->due_day && $user->due_year) {
            $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
        }

        // Get state stamp if available
        $stateStamp = null;
        if ($enrollment && $enrollment->course) {
            $stateCode = $enrollment->course->state ?? $enrollment->course->state_code ?? null;
            if ($stateCode) {
                $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                    ->where('is_active', true)
                    ->first();
            }
        }

        $data = [
            'student_name' => $request->student_name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            'student_address' => $student_address ?: null,
            'completion_date' => $request->completion_date ?: ($enrollment && $enrollment->completed_at ? $enrollment->completed_at->format('m/d/Y') : date('m/d/Y')),
            'course_type' => $request->course_name ?: ($enrollment && $enrollment->course ? $enrollment->course->title : 'Course'),
            'score' => $request->score ?: ($enrollment && $enrollment->final_exam_score ? $enrollment->final_exam_score.'%' : 'N/A'),
            'license_number' => $user->driver_license ?? null,
            'birth_date' => $birth_date,
            'citation_number' => $user->citation_number ?? null,
            'due_date' => $due_date,
            'court' => $user->court_selected ?? null,
            'county' => $user->state ?? null,
            'certificate_number' => $this->generateCertificateNumber(),
            'phone' => $phone ?: null,
            'city' => $user->city ?? null,
            'state' => $user->state ?? null,
            'zip' => $user->zip ?? null,
            'state_stamp' => $stateStamp,
        ];

        return view('certificate', $data);
    }

    public function downloadPdf(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect('/login')->with('error', 'Please login to download certificate');
        }

        // Use URL parameters if provided, otherwise fallback to database
        $enrollment = null;
        if ($request->enrollment_id) {
            $enrollment = UserCourseEnrollment::with(['course', 'user'])
                ->where('user_id', $user->id)
                ->where('id', $request->enrollment_id)
                ->first();
        }

        // Build student address
        $addressParts = array_filter([
            $user->mailing_address,
            $user->city,
            $user->state,
            $user->zip,
        ]);
        $student_address = implode(', ', $addressParts);

        // Build phone number
        $phone_parts = array_filter([$user->phone_1, $user->phone_2, $user->phone_3]);
        $phone = implode('-', $phone_parts);

        // Build birth date
        $birth_date = null;
        if ($user->birth_month && $user->birth_day && $user->birth_year) {
            $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
        }

        // Build due date
        $due_date = null;
        if ($user->due_month && $user->due_day && $user->due_year) {
            $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
        }

        $certificateNumber = $this->generateCertificateNumber();

        $data = [
            'student_name' => $request->student_name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            'student_address' => $student_address ?: null,
            'completion_date' => $request->completion_date ?: ($enrollment && $enrollment->completed_at ? $enrollment->completed_at->format('m/d/Y') : date('m/d/Y')),
            'course_type' => $request->course_name ?: ($enrollment && $enrollment->course ? $enrollment->course->title : 'Course'),
            'score' => $request->score ?: ($enrollment && $enrollment->final_exam_score ? $enrollment->final_exam_score.'%' : 'N/A'),
            'license_number' => $user->driver_license ?? null,
            'birth_date' => $birth_date,
            'citation_number' => $user->citation_number ?? null,
            'due_date' => $due_date,
            'court' => $user->court_selected ?? null,
            'county' => $user->state ?? null,
            'certificate_number' => $certificateNumber,
            'phone' => $phone ?: null,
        ];

        // Get state stamp if available
        $stateStamp = null;
        if ($enrollment && $enrollment->course) {
            $stateCode = $enrollment->course->state ?? $enrollment->course->state_code ?? null;
            if ($stateCode) {
                $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                    ->where('is_active', true)
                    ->first();
            }
        }
        $data['state_stamp'] = $stateStamp;

        // Check if PDF package is available
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificate-pdf', $data);
            $filename = 'certificate-'.($data['student_name'] ? str_replace(' ', '-', $data['student_name']) : 'user').'-'.date('Y-m-d').'.pdf';

            // Send certificate email with PDF
            try {
                $course = $enrollment ? $enrollment->course : null;
                \Mail::to($user->email)->send(new \App\Mail\CertificateGenerated(
                    $user,
                    $course,
                    $certificateNumber,
                    $pdf->output()
                ));
            } catch (\Exception $e) {
                \Log::error('Certificate email error: '.$e->getMessage());
            }

            // Handle access revocation after download
            $accessService = new CertificateAccessService;

            \Log::info('Certificate download', [
                'user_id' => $user->id,
                'enrollment_id' => $request->enrollment_id,
                'has_enrollment_id' => $request->has('enrollment_id'),
            ]);

            $result = $accessService->handleCertificateDownload($user, $request->enrollment_id);

            \Log::info('Access service result', $result);

            if ($result['status'] === 'account_locked') {
                auth()->logout();

                return redirect('/login')->with('error', $result['message']);
            }

            return $pdf->download($filename);
        }

        // Fallback: return HTML view that can be printed as PDF by browser
        return response()->view('certificate-pdf', $data)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="certificate.html"');
    }

    public function generateEnrollment(UserCourseEnrollment $enrollment)
    {
        // Check if enrollment is completed
        if (! $enrollment->completed_at) {
            return response()->json(['error' => 'Course not completed'], 400);
        }

        // Check if certificate already exists
        $existingCertificate = FloridaCertificate::where('enrollment_id', $enrollment->id)->first();
        if ($existingCertificate) {
            return view('certificates.florida-certificate', ['certificate' => $existingCertificate]);
        }

        $certificate = FloridaCertificate::create([
            'enrollment_id' => $enrollment->id,
            'certificate_number' => $this->generateCertificateNumber($enrollment->course->state_code ?? 'FL'),
            'student_name' => $enrollment->user->first_name.' '.$enrollment->user->last_name,
            'course_name' => $enrollment->course->title,
            'state_code' => $enrollment->course->state_code ?? 'FL',
            'completion_date' => $enrollment->completed_at,
            'verification_hash' => Str::random(32),
            'status' => 'generated',
        ]);

        // Dispatch certificate generated event
        event(new CertificateGenerated($certificate));

        return view('certificates.florida-certificate', ['certificate' => $certificate]);
    }

    public function verify($verificationHash)
    {
        $certificate = \App\Models\FloridaCertificate::where('verification_hash', $verificationHash)->first();

        if (! $certificate) {
            return view('certificates.verify', ['certificate' => null]);
        }

        return view('certificates.verify', compact('certificate'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'enrollment_id' => 'required|exists:user_course_enrollments,id',
                'student_name' => 'required|string',
                'course_name' => 'required|string',
                'state_code' => 'required|string|size:2',
                'completion_date' => 'required|date',
                'status' => 'required|in:generated,submitted,confirmed,failed',
            ]);

            $certificate = Certificate::create([
                'enrollment_id' => $request->enrollment_id,
                'certificate_number' => $this->generateCertificateNumber($request->state_code),
                'student_name' => $request->student_name,
                'course_name' => $request->course_name,
                'state_code' => $request->state_code,
                'completion_date' => $request->completion_date,
                'verification_hash' => Str::random(32),
                'status' => $request->status,
                'is_sent_to_state' => false,
            ]);

            return response()->json($certificate);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $certificate = FloridaCertificate::with(['enrollment.user', 'enrollment.course'])
            ->findOrFail($id);

        return response()->json($certificate);
    }

    public function update(Request $request, $id)
    {
        try {
            $certificate = FloridaCertificate::findOrFail($id);
            $certificate->update($request->all());

            return response()->json($certificate);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $certificate = FloridaCertificate::findOrFail($id);
            $certificate->delete();

            return response()->json(['message' => 'Certificate deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function submitToState($id)
    {
        $certificate = FloridaCertificate::findOrFail($id);
        $certificate->update(['is_sent_to_student' => true, 'sent_at' => now()]);

        return response()->json(['message' => 'Submitted to state', 'certificate' => $certificate]);
    }

    public function emailCertificate(Request $request, $id)
    {
        try {
            \Log::info('Starting certificate email process', ['certificate_id' => $id]);

            $certificate = FloridaCertificate::with(['enrollment.user', 'enrollment.course'])
                ->findOrFail($id);

            // Get certificate number dynamically based on state
            $certificateNumber = $this->getCertificateNumber($certificate);

            \Log::info('Certificate loaded', [
                'certificate_number' => $certificateNumber,
                'student_name' => $certificate->student_name,
                'state' => $certificate->state,
            ]);

            // Get email from request or use student's email
            $recipientEmail = $request->input('email');

            if (! $recipientEmail && $certificate->enrollment && $certificate->enrollment->user) {
                $recipientEmail = $certificate->enrollment->user->email;
            }

            if (! $recipientEmail) {
                \Log::error('No recipient email found', ['certificate_id' => $id]);

                return response()->json(['error' => 'No recipient email address found'], 400);
            }

            \Log::info('Recipient email determined', ['email' => $recipientEmail]);

            // Generate PDF using main certificate template
            \Log::info('Generating certificate PDF');
            
            // Get user data from enrollment
            $user = $certificate->enrollment->user;
            $course = $certificate->enrollment->course;
            
            // Build student address
            $addressParts = array_filter([
                $user->mailing_address,
                $user->city,
                $user->state,
                $user->zip,
            ]);
            $student_address = implode(', ', $addressParts);

            // Build birth date
            $birth_date = null;
            if ($user->birth_month && $user->birth_day && $user->birth_year) {
                $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
            }

            // Build due date
            $due_date = null;
            if ($user->due_month && $user->due_day && $user->due_year) {
                $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
            }

            // Get state stamp if available
            $stateStamp = null;
            if ($course) {
                $stateCode = $course->state ?? $course->state_code ?? null;
                if ($stateCode) {
                    $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                        ->where('is_active', true)
                        ->first();
                }
            }
            
            $templateData = [
                'student_name' => $certificate->student_name,
                'student_address' => $student_address ?: $certificate->student_address,
                'completion_date' => $certificate->completion_date->format('m/d/Y'),
                'course_type' => $certificate->course_name,
                'score' => number_format($certificate->final_exam_score, 1) . '%',
                'license_number' => $certificate->driver_license_number ?: $user->driver_license,
                'birth_date' => $birth_date ?: ($certificate->student_date_of_birth ? 
                    \Carbon\Carbon::parse($certificate->student_date_of_birth)->format('m/d/Y') : null),
                'citation_number' => $certificate->citation_number ?: $user->citation_number,
                'due_date' => $due_date ?: ($certificate->traffic_school_due_date ? 
                    \Carbon\Carbon::parse($certificate->traffic_school_due_date)->format('m/d/Y') : null),
                'court' => $certificate->court_name ?: $user->court_selected,
                'county' => $certificate->citation_county ?: $user->state,
                'certificate_number' => $certificateNumber,
                'phone' => null,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'state_stamp' => $stateStamp,
            ];
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificate-pdf', $templateData);
            $pdfOutput = $pdf->output();
            \Log::info('PDF generated successfully', ['size' => strlen($pdfOutput).' bytes']);

            // Prepare email data
            $emailData = [
                'certificate' => $certificate,
                'student_name' => $certificate->student_name,
                'certificate_number' => $certificateNumber,
                'course_name' => $certificate->course_name,
                'completion_date' => $certificate->completion_date->format('F d, Y'),
            ];

            \Log::info('Sending email', [
                'to' => $recipientEmail,
                'from' => config('mail.from.address'),
                'subject' => 'Your Course Completion Certificate',
            ]);

            // Send email
            \Mail::send('emails.certificate', $emailData, function ($message) use ($recipientEmail, $certificateNumber, $pdfOutput) {
                $message->to($recipientEmail)
                    ->subject('Your Course Completion Certificate - '.$certificateNumber)
                    ->attachData($pdfOutput, 'certificate-'.$certificateNumber.'.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

            \Log::info('Email sent successfully', ['to' => $recipientEmail]);

            // Update certificate sent status
            $certificate->update([
                'is_sent_to_student' => true,
                'sent_at' => now(),
            ]);

            \Log::info('Certificate status updated', ['certificate_id' => $id]);

            return response()->json([
                'message' => 'Certificate emailed successfully',
                'sent_to' => $recipientEmail,
                'certificate_number' => $certificateNumber,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to email certificate', [
                'certificate_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to send certificate email: '.$e->getMessage(),
            ], 500);
        }
    }

    public function download($id)
    {
        try {
            // Get certificate with enrollment and user relationships
            $certificate = FloridaCertificate::with(['enrollment.user', 'enrollment.course'])
                ->findOrFail($id);

            // Ensure user has access to this certificate
            if (auth()->check() && $certificate->enrollment->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            // Get user data from enrollment
            $user = $certificate->enrollment->user;
            $course = $certificate->enrollment->course;

            // Update certificate with user information if not already present
            $updateData = [];
            
            if (!$certificate->driver_license_number && $user->driver_license) {
                $updateData['driver_license_number'] = $user->driver_license;
            }
            
            if (!$certificate->citation_number && $user->citation_number) {
                $updateData['citation_number'] = $user->citation_number;
            }
            
            if (!$certificate->student_address && $user->mailing_address) {
                $addressParts = array_filter([
                    $user->mailing_address,
                    $user->city,
                    $user->state,
                    $user->zip,
                ]);
                $updateData['student_address'] = implode(', ', $addressParts);
            }
            
            if (!$certificate->student_date_of_birth && $user->birth_year && $user->birth_month && $user->birth_day) {
                $updateData['student_date_of_birth'] = $user->birth_year . '-' . 
                    str_pad($user->birth_month, 2, '0', STR_PAD_LEFT) . '-' . 
                    str_pad($user->birth_day, 2, '0', STR_PAD_LEFT);
            }
            
            if (!$certificate->court_name && $user->court_selected) {
                $updateData['court_name'] = $user->court_selected;
            }

            if (!$certificate->citation_county && $user->state) {
                $updateData['citation_county'] = $user->state;
            }

            if (!$certificate->traffic_school_due_date && $user->due_year && $user->due_month && $user->due_day) {
                $updateData['traffic_school_due_date'] = $user->due_year . '-' . 
                    str_pad($user->due_month, 2, '0', STR_PAD_LEFT) . '-' . 
                    str_pad($user->due_day, 2, '0', STR_PAD_LEFT);
            }

            // Update certificate if we have new data
            if (!empty($updateData)) {
                $certificate->update($updateData);
                $certificate->refresh(); // Reload the certificate with updated data
            }

            // Generate certificate number if needed
            $certificateNumber = $this->getCertificateNumber($certificate);
            
            // Build student address
            $addressParts = array_filter([
                $user->mailing_address,
                $user->city,
                $user->state,
                $user->zip,
            ]);
            $student_address = implode(', ', $addressParts);

            // Build birth date
            $birth_date = null;
            if ($user->birth_month && $user->birth_day && $user->birth_year) {
                $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
            }

            // Build due date
            $due_date = null;
            if ($user->due_month && $user->due_day && $user->due_year) {
                $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
            }

            // Get state stamp if available
            $stateStamp = null;
            if ($course) {
                $stateCode = $course->state ?? $course->state_code ?? null;
                if ($stateCode) {
                    $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                        ->where('is_active', true)
                        ->first();
                }
            }
            
            // Prepare data for the main certificate template (same format as CertificateController::generate)
            $templateData = [
                'student_name' => $certificate->student_name,
                'student_address' => $student_address ?: $certificate->student_address,
                'completion_date' => $certificate->completion_date->format('m/d/Y'),
                'course_type' => $certificate->course_name,
                'score' => number_format($certificate->final_exam_score, 1) . '%',
                'license_number' => $certificate->driver_license_number ?: $user->driver_license,
                'birth_date' => $birth_date ?: ($certificate->student_date_of_birth ? 
                    \Carbon\Carbon::parse($certificate->student_date_of_birth)->format('m/d/Y') : null),
                'citation_number' => $certificate->citation_number ?: $user->citation_number,
                'due_date' => $due_date ?: ($certificate->traffic_school_due_date ? 
                    \Carbon\Carbon::parse($certificate->traffic_school_due_date)->format('m/d/Y') : null),
                'court' => $certificate->court_name ?: $user->court_selected,
                'county' => $certificate->citation_county ?: $user->state,
                'certificate_number' => $certificateNumber,
                'phone' => null, // Not used in certificate-pdf template
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'state_stamp' => $stateStamp,
            ];
            
            // Use the main certificate template with state stamps and dynamic course types
            $html = view('certificate-pdf', $templateData)->render();

            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="certificate-'.$certificateNumber.'.html"');

        } catch (\Exception $e) {
            \Log::error('Certificate download error', [
                'certificate_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to download certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateCertificateNumber($stateCode = null)
    {
        if ($stateCode) {
            $year = date('Y');
            $lastCertificate = Certificate::where('state_code', $stateCode)
                ->whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $sequence = $lastCertificate ?
                (int) substr($lastCertificate->certificate_number, -6) + 1 : 1;

            return strtoupper($stateCode).'-'.$year.'-'.str_pad($sequence, 6, '0', STR_PAD_LEFT);
        }

        // Default certificate number for /certificate route
        return 'CERT-'.date('Y').'-'.str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get certificate number dynamically based on state
     * 
     * @param FloridaCertificate $certificate
     * @return string
     */
    private function getCertificateNumber($certificate)
    {
        // Determine state code
        $stateCode = $certificate->state ?? null;
        
        // If no state in certificate, try to get from enrollment
        if (!$stateCode && $certificate->enrollment && $certificate->enrollment->course) {
            $stateCode = $certificate->enrollment->course->state_code ?? $certificate->enrollment->course->state ?? null;
        }

        // Normalize state code to uppercase
        $stateCode = $stateCode ? strtoupper($stateCode) : 'FL';

        // For Florida, use dicds_certificate_number if available
        if ($stateCode === 'FL' && !empty($certificate->dicds_certificate_number)) {
            return $certificate->dicds_certificate_number;
        }

        // For other states or if dicds_certificate_number is not set, generate state-specific number
        // Check if certificate_number field exists (generic Certificate model)
        if (isset($certificate->certificate_number) && !empty($certificate->certificate_number)) {
            return $certificate->certificate_number;
        }

        // Fallback: generate a new certificate number based on state
        return $this->generateCertificateNumber($stateCode);
    }

    /**
     * Test certificate email functionality
     * Creates a test certificate and sends email
     */
    public function testCertificateEmail(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'enrollment_id' => 'required|exists:user_course_enrollments,id',
                'email' => 'nullable|email',
            ]);

            $enrollmentId = $request->enrollment_id;
            $testEmail = $request->email;

            // Get enrollment with relationships
            $enrollment = UserCourseEnrollment::with(['course', 'user'])
                ->findOrFail($enrollmentId);

            if (!$enrollment->user) {
                return response()->json(['error' => 'User not found for enrollment'], 404);
            }

            if (!$enrollment->course) {
                return response()->json(['error' => 'Course not found for enrollment'], 404);
            }

            // Determine state code
            $stateCode = $enrollment->course->state_code ?? $enrollment->course->state ?? 'FL';
            $stateCode = strtoupper($stateCode);

            // Check if certificate already exists
            $certificate = FloridaCertificate::where('enrollment_id', $enrollmentId)->first();

            // If no certificate exists, create a test one
            if (!$certificate) {
                $certificateNumber = $this->generateCertificateNumber($stateCode);
                
                // For all states, use the generated certificate number in dicds_certificate_number field
                // This field is required (NOT NULL) in the database
                $certificate = FloridaCertificate::create([
                    'enrollment_id' => $enrollmentId,
                    'dicds_certificate_number' => $certificateNumber,
                    'student_name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                    'course_name' => $enrollment->course->title,
                    'state' => $stateCode,
                    'completion_date' => $enrollment->completed_at ?? now(),
                    'final_exam_score' => $enrollment->final_exam_score ?? 95,
                    'driver_license_number' => $enrollment->user->driver_license,
                    'citation_number' => $enrollment->user->citation_number,
                    'student_address' => $enrollment->user->mailing_address,
                    'student_date_of_birth' => $enrollment->user->birth_year && $enrollment->user->birth_month && $enrollment->user->birth_day 
                        ? $enrollment->user->birth_year . '-' . str_pad($enrollment->user->birth_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($enrollment->user->birth_day, 2, '0', STR_PAD_LEFT)
                        : null,
                    'verification_hash' => Str::random(32),
                    'is_sent_to_student' => false,
                    'generated_at' => now(),
                ]);

                \Log::info('Test certificate created', [
                    'certificate_id' => $certificate->id,
                    'state' => $stateCode,
                    'certificate_number' => $certificateNumber,
                ]);
            }

            // Get certificate number dynamically
            $certificateNumber = $this->getCertificateNumber($certificate);

            // Determine recipient email
            $recipientEmail = $testEmail ?? $enrollment->user->email;

            if (!$recipientEmail) {
                return response()->json(['error' => 'No recipient email address found'], 400);
            }

            \Log::info('Test certificate email - Starting', [
                'certificate_id' => $certificate->id,
                'state' => $stateCode,
                'certificate_number' => $certificateNumber,
                'recipient' => $recipientEmail,
            ]);

            // Generate PDF using main certificate template
            $user = $certificate->enrollment->user;
            $course = $certificate->enrollment->course;
            
            // Build student address
            $addressParts = array_filter([
                $user->mailing_address,
                $user->city,
                $user->state,
                $user->zip,
            ]);
            $student_address = implode(', ', $addressParts);

            // Build birth date
            $birth_date = null;
            if ($user->birth_month && $user->birth_day && $user->birth_year) {
                $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
            }

            // Build due date
            $due_date = null;
            if ($user->due_month && $user->due_day && $user->due_year) {
                $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
            }

            // Get state stamp if available
            $stateStamp = null;
            if ($course) {
                $stateCode = $course->state ?? $course->state_code ?? null;
                if ($stateCode) {
                    $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                        ->where('is_active', true)
                        ->first();
                }
            }
            
            $templateData = [
                'student_name' => $certificate->student_name,
                'student_address' => $student_address ?: $certificate->student_address,
                'completion_date' => $certificate->completion_date->format('m/d/Y'),
                'course_type' => $certificate->course_name,
                'score' => number_format($certificate->final_exam_score, 1) . '%',
                'license_number' => $certificate->driver_license_number ?: $user->driver_license,
                'birth_date' => $birth_date ?: ($certificate->student_date_of_birth ? 
                    \Carbon\Carbon::parse($certificate->student_date_of_birth)->format('m/d/Y') : null),
                'citation_number' => $certificate->citation_number ?: $user->citation_number,
                'due_date' => $due_date ?: ($certificate->traffic_school_due_date ? 
                    \Carbon\Carbon::parse($certificate->traffic_school_due_date)->format('m/d/Y') : null),
                'court' => $certificate->court_name ?: $user->court_selected,
                'county' => $certificate->citation_county ?: $user->state,
                'certificate_number' => $certificateNumber,
                'phone' => null,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'state_stamp' => $stateStamp,
            ];
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificate-pdf', $templateData);
            $pdfOutput = $pdf->output();

            // Prepare email data
            $emailData = [
                'certificate' => $certificate,
                'student_name' => $certificate->student_name,
                'certificate_number' => $certificateNumber,
                'course_name' => $certificate->course_name,
                'completion_date' => $certificate->completion_date->format('F d, Y'),
            ];

            // Send email
            \Mail::send('emails.certificate', $emailData, function ($message) use ($recipientEmail, $certificateNumber, $pdfOutput) {
                $message->to($recipientEmail)
                    ->subject('TEST - Your Course Completion Certificate - ' . $certificateNumber)
                    ->attachData($pdfOutput, 'certificate-' . $certificateNumber . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

            \Log::info('Test certificate email sent successfully', [
                'certificate_id' => $certificate->id,
                'recipient' => $recipientEmail,
                'certificate_number' => $certificateNumber,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test certificate email sent successfully',
                'data' => [
                    'certificate_id' => $certificate->id,
                    'certificate_number' => $certificateNumber,
                    'state' => $stateCode,
                    'sent_to' => $recipientEmail,
                    'student_name' => $certificate->student_name,
                    'course_name' => $certificate->course_name,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Test certificate email failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send test certificate email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
