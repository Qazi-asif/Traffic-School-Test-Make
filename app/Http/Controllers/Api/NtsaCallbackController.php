<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StateTransmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NtsaCallbackController extends Controller
{
    /**
     * Handle NTSA result callback.
     */
    public function handle(Request $request)
    {
        try {
            Log::info('NTSA callback received', [
                'data' => $request->all(),
                'ip' => $request->ip(),
            ]);

            $studentId = $request->input('UniqueID');
            $percentage = $request->input('percentage');
            $testDate = $request->input('testDate');
            $certificateSentDate = $request->input('certificateSentDate');

            if (empty($studentId)) {
                Log::error('NTSA callback missing student ID');
                return response('Missing student ID', 400);
            }

            // Find the transmission
            $transmission = StateTransmission::whereHas('enrollment.user', function ($q) use ($studentId) {
                $q->where('id', $studentId);
            })
            ->where('system', 'NTSA')
            ->where('state', 'NV')
            ->orderBy('created_at', 'desc')
            ->first();

            if (!$transmission) {
                Log::warning('NTSA callback - transmission not found', [
                    'student_id' => $studentId,
                ]);
                
                return response('OK', 200);
            }

            // Update transmission with callback data
            $transmission->update([
                'status' => 'success',
                'response_code' => 'COMPLETED',
                'response_message' => "Test: {$percentage}%, Date: {$testDate}, Cert Sent: {$certificateSentDate}",
                'sent_at' => now(),
            ]);

            Log::info('NTSA callback processed', [
                'transmission_id' => $transmission->id,
                'student_id' => $studentId,
                'percentage' => $percentage,
            ]);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('NTSA callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Error processing callback', 500);
        }
    }
}
