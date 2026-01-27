<?php

namespace App\Http\Controllers;

use App\Models\FlhsmvSubmission;
use App\Services\FlhsmvSoapService;
use Illuminate\Http\Request;

class FlhsmvController extends Controller
{
    protected $flhsmvService;

    public function __construct(FlhsmvSoapService $flhsmvService)
    {
        $this->flhsmvService = $flhsmvService;
    }

    public function submitCompletion(Request $request)
    {
        $request->validate([
            'certificate_id' => 'required|exists:florida_certificates,id',
        ]);

        $result = $this->flhsmvService->submitCompletion($request->certificate_id);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Completion submitted successfully to FLHSMV',
                'submission' => $result['submission'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to submit completion',
            'error' => $result['error'] ?? 'Unknown error',
        ], 422);
    }

    public function getSubmissionStatus($submissionId)
    {
        $submission = FlhsmvSubmission::with(['errors', 'certificate'])->findOrFail($submissionId);

        return response()->json([
            'submission' => $submission,
            'status' => $submission->status,
            'errors' => $submission->errors,
        ]);
    }

    public function listSubmissions(Request $request)
    {
        // For web view
        if ($request->expectsJson() || $request->is('api/*')) {
            $submissions = FlhsmvSubmission::with(['user', 'certificate'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($submissions);
        }

        // For blade view
        return view('admin.flhsmv-submissions');
    }

    public function retrySubmission($submissionId)
    {
        $submission = FlhsmvSubmission::findOrFail($submissionId);

        $result = $this->flhsmvService->submitCompletion($submission->florida_certificate_id);

        return response()->json($result);
    }
}
