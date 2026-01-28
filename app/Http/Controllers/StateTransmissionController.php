<?php

namespace App\Http\Controllers;

use App\Models\StateTransmission;
use App\Models\Certificate;
use App\Services\StateSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StateTransmissionController extends Controller
{
    protected $submissionService;

    public function __construct(StateSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Display state transmission dashboard
     */
    public function dashboard(Request $request)
    {
        $this->authorize('admin');

        // Get filters
        $filters = [
            'state' => $request->state,
            'system' => $request->system,
            'status' => $request->status,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ];

        // Build query
        $query = StateTransmission::with(['certificate', 'enrollment.user']);

        // Apply filters
        if ($filters['state']) {
            $query->where('state', $filters['state']);
        }

        if ($filters['system']) {
            $query->where('system', $filters['system']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if ($request->search) {
            $query->whereHas('certificate', function ($q) use ($request) {
                $q->where('certificate_number', 'like', '%' . $request->search . '%')
                  ->orWhere('student_name', 'like', '%' . $request->search . '%');
            });
        }

        $transmissions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = StateTransmission::getStatistics($filters);

        // Get breakdowns
        $stateBreakdown = StateTransmission::getStateBreakdown();
        $systemBreakdown = StateTransmission::getSystemBreakdown();

        // Get recent failures
        $recentFailures = StateTransmission::getRecentFailures(5);

        return view('admin.state-transmissions.dashboard', compact(
            'transmissions',
            'stats',
            'stateBreakdown',
            'systemBreakdown',
            'recentFailures',
            'filters'
        ));
    }

    /**
     * Show transmission details
     */
    public function show(StateTransmission $transmission)
    {
        $this->authorize('admin');

        $transmission->load(['certificate', 'enrollment.user']);

        return view('admin.state-transmissions.show', compact('transmission'));
    }

    /**
     * Retry failed transmission
     */
    public function retry(StateTransmission $transmission)
    {
        $this->authorize('admin');

        if (!$transmission->canRetry()) {
            return response()->json([
                'success' => false,
                'error' => 'Transmission cannot be retried'
            ], 400);
        }

        try {
            $result = $this->submissionService->retrySubmission($transmission);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Transmission retry failed', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk retry failed transmissions
     */
    public function bulkRetry(Request $request)
    {
        $this->authorize('admin');

        $request->validate([
            'transmission_ids' => 'required|array',
            'transmission_ids.*' => 'exists:state_transmissions,id',
        ]);

        $results = [
            'total_processed' => 0,
            'successful_retries' => 0,
            'failed_retries' => 0,
            'errors' => []
        ];

        $transmissions = StateTransmission::whereIn('id', $request->transmission_ids)->get();

        foreach ($transmissions as $transmission) {
            $results['total_processed']++;

            if (!$transmission->canRetry()) {
                $results['failed_retries']++;
                $results['errors'][] = "Transmission {$transmission->id} cannot be retried";
                continue;
            }

            try {
                $result = $this->submissionService->retrySubmission($transmission);

                if ($result['success']) {
                    $results['successful_retries']++;
                } else {
                    $results['failed_retries']++;
                    $results['errors'][] = "Transmission {$transmission->id}: " . $result['error'];
                }

            } catch (\Exception $e) {
                $results['failed_retries']++;
                $results['errors'][] = "Transmission {$transmission->id}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * Submit certificate to state
     */
    public function submitCertificate(Certificate $certificate)
    {
        $this->authorize('admin');

        try {
            $result = $this->submissionService->submitCertificate($certificate);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Manual certificate submission failed', [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk submit certificates by state
     */
    public function bulkSubmitByState(Request $request)
    {
        $this->authorize('admin');

        $request->validate([
            'state_code' => 'required|string|size:2',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $result = $this->submissionService->bulkSubmitByState(
                $request->state_code,
                $request->limit ?? 50
            );

            return response()->json([
                'success' => true,
                'results' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk state submission failed', [
                'state_code' => $request->state_code,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transmission statistics API
     */
    public function statistics(Request $request)
    {
        $this->authorize('admin');

        $filters = [
            'state' => $request->state,
            'system' => $request->system,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ];

        $stats = StateTransmission::getStatistics($filters);
        $stateBreakdown = StateTransmission::getStateBreakdown();
        $systemBreakdown = StateTransmission::getSystemBreakdown();

        return response()->json([
            'statistics' => $stats,
            'state_breakdown' => $stateBreakdown,
            'system_breakdown' => $systemBreakdown,
        ]);
    }

    /**
     * Export transmission data
     */
    public function export(Request $request)
    {
        $this->authorize('admin');

        $query = StateTransmission::with(['certificate', 'enrollment.user']);

        // Apply filters
        if ($request->state) {
            $query->where('state', $request->state);
        }

        if ($request->system) {
            $query->where('system', $request->system);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transmissions = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $csv = "ID,Certificate Number,Student Name,State,System,Status,Response Code,Response Message,Sent At,Retry Count,Created At\n";

        foreach ($transmissions as $transmission) {
            $csv .= implode(',', [
                $transmission->id,
                '"' . ($transmission->certificate->certificate_number ?? 'N/A') . '"',
                '"' . ($transmission->certificate->student_name ?? 'N/A') . '"',
                $transmission->state,
                $transmission->system,
                $transmission->status,
                '"' . ($transmission->response_code ?? '') . '"',
                '"' . str_replace('"', '""', $transmission->response_message ?? '') . '"',
                $transmission->sent_at ? $transmission->sent_at->format('Y-m-d H:i:s') : '',
                $transmission->retry_count,
                $transmission->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        $filename = 'state-transmissions-' . date('Y-m-d-H-i-s') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Test state connection
     */
    public function testConnection(Request $request)
    {
        $this->authorize('admin');

        $request->validate([
            'state_code' => 'required|string|size:2',
        ]);

        $stateCode = strtoupper($request->state_code);

        try {
            $result = null;

            switch ($stateCode) {
                case 'FL':
                    $service = app(\App\Services\FloridaDicdsService::class);
                    $result = $service->testConnection();
                    break;
                case 'MO':
                    $service = app(\App\Services\MissouriDorService::class);
                    $result = $service->testConnection();
                    break;
                case 'TX':
                    $service = app(\App\Services\TexasTdlrService::class);
                    $result = $service->testConnection();
                    break;
                case 'DE':
                    $service = app(\App\Services\DelawareDmvService::class);
                    $result = $service->testConnection();
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'error' => "State connection test not implemented for: {$stateCode}"
                    ], 400);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('State connection test failed', [
                'state_code' => $stateCode,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}