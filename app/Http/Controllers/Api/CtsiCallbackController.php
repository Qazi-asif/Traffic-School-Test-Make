<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StateTransmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CtsiCallbackController extends Controller
{
    /**
     * Handle CTSI result callback (XML POST).
     */
    public function handle(Request $request)
    {
        try {
            $xmlString = $request->getContent();

            Log::info('CTSI callback received', [
                'xml' => $xmlString,
                'ip' => $request->ip(),
            ]);

            // Parse XML
            $xml = simplexml_load_string($xmlString);
            
            if ($xml === false) {
                Log::error('Failed to parse CTSI XML', ['xml' => $xmlString]);
                return response('Invalid XML', 400);
            }

            $studentId = (string) ($xml->vscid ?? $xml->studentId ?? '');
            $keyResponse = (string) ($xml->keyresponse ?? '');
            $saveData = (string) ($xml->saveData ?? '');
            $processDate = (string) ($xml->processDate ?? now());

            if (empty($studentId)) {
                Log::error('CTSI callback missing student ID', ['xml' => $xmlString]);
                return response('Missing student ID', 400);
            }

            // Find the transmission by enrollment user ID
            $transmission = StateTransmission::whereHas('enrollment.user', function ($q) use ($studentId) {
                $q->where('id', $studentId);
            })
            ->where('system', 'CTSI')
            ->where('state', 'CA')
            ->orderBy('created_at', 'desc')
            ->first();

            if (!$transmission) {
                Log::warning('CTSI callback - transmission not found', [
                    'student_id' => $studentId,
                ]);
                
                // Still return success to CTSI
                return response('OK', 200);
            }

            // Update transmission
            $transmission->update([
                'status' => 'success',
                'response_code' => $keyResponse,
                'response_message' => $saveData,
                'sent_at' => now(),
            ]);

            Log::info('CTSI callback processed', [
                'transmission_id' => $transmission->id,
                'student_id' => $studentId,
                'key_response' => $keyResponse,
            ]);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('CTSI callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Error processing callback', 500);
        }
    }
}
