<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StateTransmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CcsCallbackController extends Controller
{
    /**
     * Handle CCS result callback.
     */
    public function handle(Request $request)
    {
        try {
            Log::info('CCS callback received', [
                'data' => $request->all(),
                'ip' => $request->ip(),
            ]);

            $studentId = $request->input('StudentUserID');
            $status = $request->input('Status');
            $percentage = $request->input('Percentage');
            $testDate = $request->input('TestDate');
            $certificateSentDate = $request->input('CertificateSentDate');

            if (empty($studentId)) {
                Log::error('CCS callback missing student ID');
                return response('Missing student ID', 400);
            }

            // Find the transmission
            $transmission = StateTransmission::whereHas('enrollment.user', function ($q) use ($studentId) {
                $q->where('id', $studentId);
            })
            ->where('system', 'CCS')
            ->orderBy('created_at', 'desc')
            ->first();

            if (!$transmission) {
                Log::warning('CCS callback - transmission not found', [
                    'student_id' => $studentId,
                ]);
                
                return response('OK', 200);
            }

            // Update transmission
            $isSuccess = strtolower($status) === 'pass';
            
            $transmission->update([
                'status' => $isSuccess ? 'success' : 'error',
                'response_code' => strtoupper($status),
                'response_message' => "Status: {$status}, Score: {$percentage}%, Test: {$testDate}, Cert: {$certificateSentDate}",
                'sent_at' => now(),
            ]);

            // If passed, update enrollment status
            if ($isSuccess && $transmission->enrollment) {
                $transmission->enrollment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            Log::info('CCS callback processed', [
                'transmission_id' => $transmission->id,
                'student_id' => $studentId,
                'status' => $status,
                'percentage' => $percentage,
            ]);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('CCS callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Error processing callback', 500);
        }
    }
}
