<?php

namespace App\Services;

use App\Models\CtsiResult;
use App\Models\UserCourseEnrollment;
use Exception;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class CtsiService
{
    /**
     * Parse CTSI XML callback and store result.
     */
    public function parseCallback(string $xmlContent): array
    {
        try {
            Log::info('CTSI Callback Received', ['xml' => $xmlContent]);

            // Parse XML
            $xml = new SimpleXMLElement($xmlContent);

            // Extract data from XML
            $enrollmentId = (string) $xml->vscid ?? null;
            $keyResponse = (string) $xml->keyresponse ?? null;
            $saveData = (string) $xml->saveData ?? null;
            $processDate = (string) $xml->processDate ?? null;

            if (!$enrollmentId) {
                throw new Exception('Missing enrollment ID (vscid) in XML');
            }

            // Find enrollment
            $enrollment = UserCourseEnrollment::find($enrollmentId);

            if (!$enrollment) {
                throw new Exception("Enrollment {$enrollmentId} not found");
            }

            // Determine status based on key response
            $status = $this->determineStatus($keyResponse);

            // Store CTSI result
            $result = CtsiResult::create([
                'enrollment_id' => $enrollmentId,
                'key_response' => $keyResponse,
                'save_data' => $saveData,
                'process_date' => $processDate ? now()->parse($processDate) : now(),
                'raw_xml' => $xmlContent,
                'status' => $status,
            ]);

            // Update enrollment if successful
            if ($status === 'success') {
                $this->updateEnrollment($enrollment, $result);
            }

            Log::info('CTSI Result Stored', [
                'result_id' => $result->id,
                'enrollment_id' => $enrollmentId,
                'status' => $status,
            ]);

            return [
                'success' => true,
                'result' => $result,
                'message' => 'CTSI callback processed successfully',
            ];
        } catch (Exception $e) {
            Log::error('CTSI Callback Error', [
                'error' => $e->getMessage(),
                'xml' => $xmlContent,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Determine status from key response.
     */
    protected function determineStatus(?string $keyResponse): string
    {
        if (!$keyResponse) {
            return 'pending';
        }

        // Success indicators (adjust based on actual CTSI responses)
        $successCodes = ['SUCCESS', 'PASS', 'COMPLETED', 'APPROVED'];

        foreach ($successCodes as $code) {
            if (stripos($keyResponse, $code) !== false) {
                return 'success';
            }
        }

        // Failure indicators
        $failureCodes = ['FAIL', 'ERROR', 'REJECTED', 'DENIED'];

        foreach ($failureCodes as $code) {
            if (stripos($keyResponse, $code) !== false) {
                return 'failed';
            }
        }

        return 'pending';
    }

    /**
     * Update enrollment based on CTSI result.
     */
    protected function updateEnrollment(UserCourseEnrollment $enrollment, CtsiResult $result): void
    {
        // Mark as completed if not already
        if ($enrollment->status !== 'completed') {
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'progress' => 100,
            ]);
        }

        // Update California certificate if exists
        if ($certificate = $enrollment->californiaCertificate) {
            $certificate->update([
                'court_code' => $result->key_response,
                'status' => 'sent',
                'sent_at' => $result->process_date,
            ]);
        }

        Log::info('Enrollment updated from CTSI', [
            'enrollment_id' => $enrollment->id,
            'result_id' => $result->id,
        ]);
    }

    /**
     * Check if CTSI is enabled.
     */
    public function isEnabled(): bool
    {
        return config('california.ctsi.enabled') === true;
    }

    /**
     * Get CTSI result URL.
     */
    public function getResultUrl(): string
    {
        return config('california.ctsi.result_url') ?? route('api.ctsi.result');
    }
}
