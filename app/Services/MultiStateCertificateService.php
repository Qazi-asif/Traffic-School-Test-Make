<?php

namespace App\Services;

use App\Models\UserCourseEnrollment;
use App\Models\FloridaCourse;
use App\Models\Missouri\Course as MissouriCourse;
use App\Models\Texas\Course as TexasCourse;
use App\Models\Delaware\Course as DelawareCourse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MultiStateCertificateService
{
    /**
     * Generate state-specific certificate
     */
    public function generateCertificate(UserCourseEnrollment $enrollment)
    {
        $course = $this->getCourseData($enrollment);
        $stateCode = $this->getStateCode($enrollment, $course);
        
        Log::info("Generating certificate for state: {$stateCode}", [
            'enrollment_id' => $enrollment->id,
            'course_table' => $enrollment->course_table
        ]);
        
        switch (strtoupper($stateCode)) {
            case 'FL':
                return $this->generateFloridaCertificate($enrollment, $course);
            case 'MO':
                return $this->generateMissouriCertificate($enrollment, $course);
            case 'TX':
                return $this->generateTexasCertificate($enrollment, $course);
            case 'DE':
                return $this->generateDelawareCertificate($enrollment, $course);
            default:
                return $this->generateGenericCertificate($enrollment, $course);
        }
    }
    
    /**
     * Generate Florida-specific certificate
     */
    private function generateFloridaCertificate(UserCourseEnrollment $enrollment, $course)
    {
        $certificateData = [
            'student_name' => $enrollment->user->name,
            'course_title' => $course->title,
            'completion_date' => $enrollment->completed_at->format('F j, Y'),
            'dicds_course_id' => $course->dicds_course_id ?? 'FL-BDI-001',
            'certificate_number' => $this->generateCertificateNumber('FL', $enrollment->id),
            'state_seal' => 'florida_state_seal.png',
            'template' => $course->certificate_template ?? 'florida_bdi_certificate',
            'state_requirements' => [
                'minimum_hours' => $course->duration / 60,
                'passing_score' => $course->passing_score,
                'dicds_submission_required' => true
            ]
        ];
        
        return $this->renderCertificate('florida', $certificateData);
    }
    
    /**
     * Generate Missouri-specific certificate with Form 4444
     */
    private function generateMissouriCertificate(UserCourseEnrollment $enrollment, $course)
    {
        $certificateData = [
            'student_name' => $enrollment->user->name,
            'course_title' => $course->title,
            'completion_date' => $enrollment->completed_at->format('F j, Y'),
            'course_code' => $course->missouri_course_code ?? 'MO-DD-001',
            'certificate_number' => $this->generateCertificateNumber('MO', $enrollment->id),
            'state_seal' => 'missouri_state_seal.png',
            'template' => $course->form_4444_template ?? 'missouri_form_4444',
            'approval_number' => $course->approval_number,
            'required_hours' => $course->required_hours,
            'state_requirements' => [
                'minimum_hours' => $course->required_hours,
                'passing_score' => 70,
                'form_4444_required' => $course->requires_form_4444
            ]
        ];
        
        // Generate Form 4444 if required
        if ($course->requires_form_4444) {
            $this->generateMissouriForm4444($enrollment, $certificateData);
        }
        
        return $this->renderCertificate('missouri', $certificateData);
    }
    
    /**
     * Generate Texas-specific certificate
     */
    private function generateTexasCertificate(UserCourseEnrollment $enrollment, $course)
    {
        $certificateData = [
            'student_name' => $enrollment->user->name,
            'course_title' => $course->title,
            'completion_date' => $enrollment->completed_at->format('F j, Y'),
            'tdlr_course_id' => $course->tdlr_course_id ?? 'TDLR-2024-001',
            'certificate_number' => $this->generateCertificateNumber('TX', $enrollment->id),
            'state_seal' => 'texas_state_seal.png',
            'template' => $course->certificate_template ?? 'texas_dd_certificate',
            'approval_number' => $course->approval_number,
            'defensive_driving_hours' => $course->defensive_driving_hours,
            'state_requirements' => [
                'minimum_hours' => $course->required_hours,
                'passing_score' => 75,
                'tdlr_approved' => true
            ]
        ];
        
        return $this->renderCertificate('texas', $certificateData);
    }
    
    /**
     * Generate Delaware-specific certificate
     */
    private function generateDelawareCertificate(UserCourseEnrollment $enrollment, $course)
    {
        $certificateData = [
            'student_name' => $enrollment->user->name,
            'course_title' => $course->title,
            'completion_date' => $enrollment->completed_at->format('F j, Y'),
            'course_code' => $course->delaware_course_code ?? 'DE-DD-6HR',
            'certificate_number' => $this->generateCertificateNumber('DE', $enrollment->id),
            'state_seal' => 'delaware_state_seal.png',
            'template' => $course->certificate_template ?? 'delaware_dd_certificate',
            'approval_number' => $course->approval_number,
            'duration_type' => $course->duration_type ?? '6hr',
            'state_requirements' => [
                'minimum_hours' => $course->required_hours,
                'passing_score' => 80,
                'quiz_rotation_used' => $course->quiz_rotation_enabled
            ]
        ];
        
        return $this->renderCertificate('delaware', $certificateData);
    }
    
    /**
     * Generate generic certificate for unsupported states
     */
    private function generateGenericCertificate(UserCourseEnrollment $enrollment, $course)
    {
        $certificateData = [
            'student_name' => $enrollment->user->name,
            'course_title' => $course->title ?? 'Defensive Driving Course',
            'completion_date' => $enrollment->completed_at->format('F j, Y'),
            'certificate_number' => $this->generateCertificateNumber('GEN', $enrollment->id),
            'template' => 'generic_certificate',
            'state_requirements' => [
                'minimum_hours' => 4,
                'passing_score' => 80
            ]
        ];
        
        return $this->renderCertificate('generic', $certificateData);
    }
    
    /**
     * Generate Missouri Form 4444
     */
    private function generateMissouriForm4444(UserCourseEnrollment $enrollment, array $certificateData)
    {
        $form4444Data = [
            'student_name' => $enrollment->user->name,
            'student_address' => $enrollment->user->address ?? '',
            'student_city' => $enrollment->user->city ?? '',
            'student_state' => $enrollment->user->state ?? 'MO',
            'student_zip' => $enrollment->user->zip_code ?? '',
            'drivers_license' => $enrollment->user->drivers_license ?? '',
            'date_of_birth' => $enrollment->user->date_of_birth ?? '',
            'course_completion_date' => $enrollment->completed_at->format('m/d/Y'),
            'course_name' => $certificateData['course_title'],
            'approval_number' => $certificateData['approval_number'],
            'hours_completed' => $certificateData['required_hours'],
            'school_name' => config('app.name', 'Traffic School'),
            'form_number' => 'MO-4444-' . $enrollment->id
        ];
        
        // Store Form 4444 data in database
        \DB::table('missouri_form_4444s')->updateOrInsert(
            ['enrollment_id' => $enrollment->id],
            array_merge($form4444Data, [
                'created_at' => now(),
                'updated_at' => now()
            ])
        );
        
        return $form4444Data;
    }
    
    /**
     * Render certificate using appropriate template
     */
    private function renderCertificate(string $state, array $data)
    {
        $templatePath = "certificates.{$state}";
        
        try {
            // Use Laravel's view system to render the certificate
            $html = view($templatePath, $data)->render();
            
            // Generate PDF using DomPDF or similar
            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML($html);
            $pdf->setPaper('letter', 'landscape');
            
            return [
                'success' => true,
                'pdf_content' => $pdf->output(),
                'filename' => "certificate_{$state}_{$data['certificate_number']}.pdf",
                'certificate_data' => $data
            ];
            
        } catch (\Exception $e) {
            Log::error("Certificate generation failed for state {$state}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'certificate_data' => $data
            ];
        }
    }
    
    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber(string $stateCode, int $enrollmentId): string
    {
        $timestamp = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "{$stateCode}-{$timestamp}-{$enrollmentId}-{$random}";
    }
    
    /**
     * Get course data based on enrollment
     */
    private function getCourseData(UserCourseEnrollment $enrollment)
    {
        switch ($enrollment->course_table) {
            case 'florida_courses':
                return FloridaCourse::find($enrollment->course_id);
            case 'missouri_courses':
                return MissouriCourse::find($enrollment->course_id);
            case 'texas_courses':
                return TexasCourse::find($enrollment->course_id);
            case 'delaware_courses':
                return DelawareCourse::find($enrollment->course_id);
            default:
                return \App\Models\Course::find($enrollment->course_id);
        }
    }
    
    /**
     * Get state code from enrollment and course
     */
    private function getStateCode(UserCourseEnrollment $enrollment, $course): string
    {
        if (isset($course->state_code)) {
            return $course->state_code;
        }
        
        if (isset($course->state)) {
            return $course->state;
        }
        
        // Determine from course table
        switch ($enrollment->course_table) {
            case 'florida_courses':
                return 'FL';
            case 'missouri_courses':
                return 'MO';
            case 'texas_courses':
                return 'TX';
            case 'delaware_courses':
                return 'DE';
            default:
                return 'GENERIC';
        }
    }
}