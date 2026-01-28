<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use App\Jobs\SendFloridaTransmissionJob;
use App\Jobs\SendMissouriTransmissionJob;
use App\Jobs\SendTexasTransmissionJob;
use App\Jobs\SendDelawareTransmissionJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class StateSubmissionService
{
    /**
     * Submit certificate to appropriate state authority
     */
    public function submitCertificate(Certificate $certificate): array
    {
        $stateCode = strtoupper($certificate->state_code);
        
        Log::info("Initiating state submission for certificate", [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'state_code' => $stateCode
        ]);
        
        // Check if already submitted
        if ($certificate->is_sent_to_state) {
            return [
                'success' => false,
                'error' => 'Certificate already submitted to state',
                'submission_id' => $certificate->state_submission_id
            ];
        }
        
        // Validate certificate meets state requirements
        if (!$certificate->meetsStateRequirements()) {
            return [
                'success' => false,
                'error' => 'Certificate does not meet state requirements',
                'requirements' => $certificate->state_requirements
            ];
        }
        
        try {
            switch ($stateCode) {
                case 'FL':
                    return $this->submitToFlorida($certificate);
                case 'MO':
                    return $this->submitToMissouri($certificate);
                case 'TX':
                    return $this->submitToTexas($certificate);
                case 'DE':
                    return $this->submitToDelaware($certificate);
                default:
                    return [
                        'success' => false,
                        'error' => "State submission not implemented for: {$stateCode}"
                    ];
            }
        } catch (\Exception $e) {
            Log::error("State submission failed", [
                'certificate_id' => $certificate->id,
                'state_code' => $stateCode,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Submit certificate to Florida DICDS system
     */
    private function submitToFlorida(Certificate $certificate): array
    {
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;
        
        // Create state transmission record
        $transmission = StateTransmission::create([
            'certificate_id' => $certificate->id,
            'enrollment_id' => $enrollment->id,
            'state' => 'FL',
            'system' => 'DICDS',
            'status' => 'pending',
            'payload_json' => [
                'certificate_number' => $certificate->certificate_number,
                'student_name' => $certificate->student_name,
                'driver_license_number' => $certificate->driver_license_number,
                'citation_number' => $certificate->citation_number,
                'completion_date' => $certificate->completion_date->format('Y-m-d'),
                'final_exam_score' => $certificate->final_exam_score,
                'course_type' => $certificate->certificate_type,
                'court_name' => $certificate->court_name,
                'county' => $certificate->citation_county,
            ],
            'retry_count' => 0,
        ]);
        
        // Queue Florida DICDS submission job
        SendFloridaTransmissionJob::dispatch($transmission);
        
        Log::info("Florida DICDS submission queued", [
            'transmission_id' => $transmission->id,
            'certificate_id' => $certificate->id
        ]);
        
        return [
            'success' => true,
            'message' => 'Florida DICDS submission queued',
            'transmission_id' => $transmission->id,
            'system' => 'DICDS'
        ];
    }
    
    /**
     * Submit certificate to Missouri Department of Revenue
     */
    private function submitToMissouri(Certificate $certificate): array
    {
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;
        
        // Get Missouri-specific certificate data
        $missouriCert = \App\Models\MissouriCertificate::where('enrollment_id', $enrollment->id)->first();
        
        // Create state transmission record
        $transmission = StateTransmission::create([
            'certificate_id' => $certificate->id,
            'enrollment_id' => $enrollment->id,
            'state' => 'MO',
            'system' => 'DOR',
            'status' => 'pending',
            'payload_json' => [
                'certificate_number' => $certificate->certificate_number,
                'student_name' => $certificate->student_name,
                'student_address' => $certificate->student_address,
                'driver_license_number' => $certificate->driver_license_number,
                'completion_date' => $certificate->completion_date->format('Y-m-d'),
                'final_exam_score' => $certificate->final_exam_score,
                'course_hours' => $missouriCert->required_hours ?? 8,
                'form_4444_number' => $missouriCert->form_4444_number ?? null,
                'approval_number' => $missouriCert->approval_number ?? null,
            ],
            'retry_count' => 0,
        ]);
        
        // Queue Missouri DOR submission job
        SendMissouriTransmissionJob::dispatch($transmission);
        
        Log::info("Missouri DOR submission queued", [
            'transmission_id' => $transmission->id,
            'certificate_id' => $certificate->id
        ]);
        
        return [
            'success' => true,
            'message' => 'Missouri DOR submission queued',
            'transmission_id' => $transmission->id,
            'system' => 'DOR'
        ];
    }
    
    /**
     * Submit certificate to Texas TDLR
     */
    private function submitToTexas(Certificate $certificate): array
    {
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;
        
        // Get Texas-specific certificate data
        $texasCert = \App\Models\TexasCertificate::where('enrollment_id', $enrollment->id)->first();
        
        // Create state transmission record
        $transmission = StateTransmission::create([
            'certificate_id' => $certificate->id,
            'enrollment_id' => $enrollment->id,
            'state' => 'TX',
            'system' => 'TDLR',
            'status' => 'pending',
            'payload_json' => [
                'certificate_number' => $certificate->certificate_number,
                'student_name' => $certificate->student_name,
                'driver_license_number' => $certificate->driver_license_number,
                'citation_number' => $certificate->citation_number,
                'completion_date' => $certificate->completion_date->format('Y-m-d'),
                'final_exam_score' => $certificate->final_exam_score,
                'course_hours' => $texasCert->defensive_driving_hours ?? 6,
                'tdlr_course_id' => $texasCert->tdlr_course_id ?? null,
                'court_name' => $certificate->court_name,
                'county' => $certificate->citation_county,
            ],
            'retry_count' => 0,
        ]);
        
        // Queue Texas TDLR submission job
        SendTexasTransmissionJob::dispatch($transmission);
        
        Log::info("Texas TDLR submission queued", [
            'transmission_id' => $transmission->id,
            'certificate_id' => $certificate->id
        ]);
        
        return [
            'success' => true,
            'message' => 'Texas TDLR submission queued',
            'transmission_id' => $transmission->id,
            'system' => 'TDLR'
        ];
    }
    
    /**
     * Submit certificate to Delaware DMV
     */
    private function submitToDelaware(Certificate $certificate): array
    {
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;
        
        // Get Delaware-specific certificate data
        $delawareCert = \App\Models\DelawareCertificate::where('enrollment_id', $enrollment->id)->first();
        
        // Create state transmission record
        $transmission = StateTransmission::create([
            'certificate_id' => $certificate->id,
            'enrollment_id' => $enrollment->id,
            'state' => 'DE',
            'system' => 'DMV',
            'status' => 'pending',
            'payload_json' => [
                'certificate_number' => $certificate->certificate_number,
                'student_name' => $certificate->student_name,
                'driver_license_number' => $certificate->driver_license_number,
                'completion_date' => $certificate->completion_date->format('Y-m-d'),
                'final_exam_score' => $certificate->final_exam_score,
                'course_type' => $delawareCert->course_duration_type ?? '6hr',
                'course_hours' => $delawareCert->required_hours ?? 6,
                'quiz_rotation_used' => $delawareCert->quiz_rotation_enabled ?? true,
            ],
            'retry_count' => 0,
        ]);
        
        // Queue Delaware DMV submission job
        SendDelawareTransmissionJob::dispatch($transmission);
        
        Log::info("Delaware DMV submission queued", [
            'transmission_id' => $transmission->id,
            'certificate_id' => $certificate->id
        ]);
        
        return [
            'success' => true,
            'message' => 'Delaware DMV submission queued',
            'transmission_id' => $transmission->id,
            'system' => 'DMV'
        ];
    }
    
    /**
     * Retry failed state submission
     */
    public function retrySubmission(StateTransmission $transmission): array
    {
        if ($transmission->status === 'success') {
            return [
                'success' => false,
                'error' => 'Transmission already successful'
            ];
        }
        
        if ($transmission->retry_count >= 3) {
            return [
                'success' => false,
                'error' => 'Maximum retry attempts exceeded'
            ];
        }
        
        // Increment retry count
        $transmission->increment('retry_count');
        $transmission->update(['status' => 'pending']);
        
        // Re-queue appropriate job
        switch (strtoupper($transmission->state)) {
            case 'FL':
                SendFloridaTransmissionJob::dispatch($transmission);
                break;
            case 'MO':
                SendMissouriTransmissionJob::dispatch($transmission);
                break;
            case 'TX':
                SendTexasTransmissionJob::dispatch($transmission);
                break;
            case 'DE':
                SendDelawareTransmissionJob::dispatch($transmission);
                break;
            default:
                return [
                    'success' => false,
                    'error' => 'Unknown state: ' . $transmission->state
                ];
        }
        
        Log::info("State submission retry queued", [
            'transmission_id' => $transmission->id,
            'retry_count' => $transmission->retry_count
        ]);
        
        return [
            'success' => true,
            'message' => 'Submission retry queued',
            'retry_count' => $transmission->retry_count
        ];
    }
    
    /**
     * Get submission status for certificate
     */
    public function getSubmissionStatus(Certificate $certificate): array
    {
        $transmissions = StateTransmission::where('certificate_id', $certificate->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($transmissions->isEmpty()) {
            return [
                'status' => 'not_submitted',
                'message' => 'No state submissions found'
            ];
        }
        
        $latest = $transmissions->first();
        
        return [
            'status' => $latest->status,
            'system' => $latest->system,
            'submitted_at' => $latest->sent_at,
            'response_code' => $latest->response_code,
            'response_message' => $latest->response_message,
            'retry_count' => $latest->retry_count,
            'all_transmissions' => $transmissions->toArray()
        ];
    }
    
    /**
     * Bulk submit certificates for a state
     */
    public function bulkSubmitByState(string $stateCode, int $limit = 50): array
    {
        $certificates = Certificate::where('state_code', strtoupper($stateCode))
            ->where('is_sent_to_state', false)
            ->where('status', 'generated')
            ->limit($limit)
            ->get();
        
        $results = [
            'total_processed' => 0,
            'successful_queued' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($certificates as $certificate) {
            $results['total_processed']++;
            
            $result = $this->submitCertificate($certificate);
            
            if ($result['success']) {
                $results['successful_queued']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'certificate_id' => $certificate->id,
                    'error' => $result['error']
                ];
            }
        }
        
        Log::info("Bulk state submission completed", [
            'state_code' => $stateCode,
            'results' => $results
        ]);
        
        return $results;
    }
}