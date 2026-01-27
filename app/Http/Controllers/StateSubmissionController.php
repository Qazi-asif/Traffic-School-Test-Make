<?php

namespace App\Http\Controllers;

use App\Models\StateSubmissionQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StateSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = StateSubmissionQueue::with(['certificate', 'stateConfiguration']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('state_code')) {
            $query->whereHas('stateConfiguration', function ($q) use ($request) {
                $q->where('state_code', $request->state_code);
            });
        }

        $submissions = $query->orderBy('priority', 'desc')
            ->orderBy('next_attempt_at', 'asc')
            ->paginate(20);

        return response()->json($submissions);
    }

    public function retry($id)
    {
        $submission = StateSubmissionQueue::findOrFail($id);

        if ($submission->status === 'completed') {
            return response()->json(['error' => 'Cannot retry completed submission'], 400);
        }

        $submission->update([
            'status' => 'pending',
            'next_attempt_at' => now(),
            'error_message' => null,
        ]);

        return response()->json(['message' => 'Submission queued for retry']);
    }

    public function processPending()
    {
        $pendingSubmissions = StateSubmissionQueue::where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('next_attempt_at')
                    ->orWhere('next_attempt_at', '<=', now());
            })
            ->with(['certificate', 'stateConfiguration'])
            ->orderBy('priority', 'desc')
            ->limit(10)
            ->get();

        $processed = 0;
        foreach ($pendingSubmissions as $submission) {
            try {
                $this->processSubmission($submission);
                $processed++;
            } catch (\Exception $e) {
                \Log::error('Failed to process submission: '.$e->getMessage(), [
                    'submission_id' => $submission->id,
                ]);
            }
        }

        return response()->json([
            'message' => "Processed {$processed} submissions",
            'processed_count' => $processed,
        ]);
    }

    public function stats()
    {
        $stats = [
            'pending' => StateSubmissionQueue::where('status', 'pending')->count(),
            'processing' => StateSubmissionQueue::where('status', 'processing')->count(),
            'completed' => StateSubmissionQueue::where('status', 'completed')->count(),
            'failed' => StateSubmissionQueue::where('status', 'failed')->count(),
            'retry' => StateSubmissionQueue::where('status', 'retry')->count(),
            'high_priority' => StateSubmissionQueue::where('priority', 'high')->whereIn('status', ['pending', 'retry'])->count(),
            'overdue' => StateSubmissionQueue::where('next_attempt_at', '<', now())->whereIn('status', ['pending', 'retry'])->count(),
        ];

        return response()->json($stats);
    }

    private function processSubmission($submission)
    {
        $submission->update([
            'status' => 'processing',
            'last_attempt_at' => now(),
            'attempts' => $submission->attempts + 1,
        ]);

        try {
            $config = $submission->stateConfiguration;
            $certificate = $submission->certificate;

            switch ($config->submission_method) {
                case 'api':
                    $result = $this->submitViaApi($config, $certificate);
                    break;
                case 'portal':
                    $result = $this->submitViaPortal($config, $certificate);
                    break;
                case 'email':
                    $result = $this->submitViaEmail($config, $certificate);
                    break;
                default:
                    throw new \Exception('Unsupported submission method');
            }

            $submission->update([
                'status' => 'completed',
                'processed_at' => now(),
                'response_data' => $result,
                'error_message' => null,
            ]);

        } catch (\Exception $e) {
            $this->handleSubmissionFailure($submission, $e);
        }
    }

    private function submitViaApi($config, $certificate)
    {
        $credentials = $config->api_credentials;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$credentials['token'] ?? '',
            'Content-Type' => 'application/json',
        ])->post($config->api_endpoint, [
            'certificate_id' => $certificate->id,
            'student_name' => $certificate->student_name,
            'completion_date' => $certificate->completion_date,
            'course_name' => $certificate->course_name,
        ]);

        if (! $response->successful()) {
            throw new \Exception('API submission failed: '.$response->body());
        }

        return $response->json();
    }

    private function submitViaPortal($config, $certificate)
    {
        // This would integrate with browser automation (Selenium/Puppeteer)
        // For now, return mock success
        return ['status' => 'submitted', 'portal_id' => 'MOCK_'.time()];
    }

    private function submitViaEmail($config, $certificate)
    {
        \Mail::to($config->email_recipient)->send(new \App\Mail\CertificateSubmission($certificate));

        return ['status' => 'emailed', 'recipient' => $config->email_recipient];
    }

    private function handleSubmissionFailure($submission, $exception)
    {
        $nextStatus = 'failed';
        $nextAttempt = null;

        if ($submission->attempts < $submission->max_attempts) {
            $nextStatus = 'retry';
            // Exponential backoff: 5 min, 30 min, 2 hours
            $delays = [5, 30, 120];
            $delayMinutes = $delays[min($submission->attempts - 1, count($delays) - 1)];
            $nextAttempt = now()->addMinutes($delayMinutes);
        }

        $submission->update([
            'status' => $nextStatus,
            'next_attempt_at' => $nextAttempt,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
