<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendFloridaTransmissionJob;
use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use App\Services\FlhsmvSoapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlTransmissionController extends Controller
{
    /**
     * Display pending and error transmissions.
     */
    public function index(Request $request)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $query = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('enrollment.user', function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $transmissions = $query->paginate(50);

        // Separate by status for display - filter out orphaned transmissions
        $pending = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->pending()
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->orderBy('created_at', 'asc')
            ->paginate(25, ['*'], 'pending_page');

        $errors = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->error()
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'error_page');

        $successful = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->success()
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->orderBy('sent_at', 'desc')
            ->paginate(25, ['*'], 'success_page');

        return view('admin.fl-transmissions.index', compact('pending', 'errors', 'successful'));
    }

    /**
     * Send a single transmission.
     */
    public function sendSingle(Request $request, $id)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::findOrFail($id);

        if ($transmission->status !== 'pending') {
            return back()->with('error', 'Only pending transmissions can be sent.');
        }

        try {
            SendFloridaTransmissionJob::dispatch($transmission->id);

            Log::info('Manual transmission dispatch', [
                'transmission_id' => $transmission->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Transmission queued successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to dispatch transmission', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to queue transmission: '.$e->getMessage());
        }
    }

    /**
     * Send all pending transmissions.
     */
    public function sendAll(Request $request)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $pendingTransmissions = StateTransmission::forState('FL')
            ->pending()
            ->get();

        if ($pendingTransmissions->isEmpty()) {
            return back()->with('info', 'No pending transmissions to send.');
        }

        $count = 0;
        foreach ($pendingTransmissions as $transmission) {
            try {
                SendFloridaTransmissionJob::dispatch($transmission->id);
                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to dispatch transmission in batch', [
                    'transmission_id' => $transmission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Batch transmission dispatch', [
            'count' => $count,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', "Successfully queued {$count} transmissions.");
    }

    /**
     * Retry a failed transmission.
     */
    public function retry(Request $request, $id)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::findOrFail($id);

        if ($transmission->status !== 'error') {
            return back()->with('error', 'Only error transmissions can be retried.');
        }

        try {
            // Reset to pending
            $transmission->update([
                'status' => 'pending',
                'response_code' => null,
                'response_message' => null,
            ]);

            // Dispatch job
            SendFloridaTransmissionJob::dispatch($transmission->id);

            Log::info('Manual transmission retry', [
                'transmission_id' => $transmission->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Transmission retry queued successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to retry transmission', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to retry transmission: '.$e->getMessage());
        }
    }

    /**
     * View transmission details.
     */
    public function show($id)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::with(['enrollment.user', 'enrollment.course', 'enrollment.floridaCertificate'])
            ->findOrFail($id);

        return view('admin.fl-transmissions.show', compact('transmission'));
    }

    /**
     * Delete a transmission record.
     */
    public function destroy($id)
    {
        // Authorization check - only super admin
        if (! auth()->check() || ! auth()->user()->role || auth()->user()->role->slug !== 'super-admin') {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::findOrFail($id);
        $transmission->delete();

        Log::info('Transmission deleted', [
            'transmission_id' => $id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Transmission deleted successfully.');
    }

    /**
     * Test Florida API connection.
     */
    public function testConnection()
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access');
        }

        try {
            $service = new FlhsmvSoapService();
            $result = $service->testConnection();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Florida API connection successful',
                    'details' => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Florida API connection failed',
                    'error' => $result['error'],
                    'suggestion' => $result['suggestion'] ?? null,
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create manual transmission for completed enrollment.
     */
    public function createManual(Request $request)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
        ]);

        try {
            $enrollment = UserCourseEnrollment::with(['user', 'course'])->findOrFail($request->enrollment_id);

            // Check if enrollment is completed
            if (!$enrollment->completed_at) {
                return back()->with('error', 'Enrollment must be completed before creating transmission');
            }

            // Check if transmission already exists
            $existingTransmission = StateTransmission::where('enrollment_id', $enrollment->id)
                ->where('state', 'FL')
                ->first();

            if ($existingTransmission) {
                return back()->with('error', 'Transmission already exists for this enrollment');
            }

            // Create new transmission
            $transmission = StateTransmission::create([
                'enrollment_id' => $enrollment->id,
                'state' => 'FL',
                'system' => 'FLHSMV',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            // Dispatch job
            SendFloridaTransmissionJob::dispatch($transmission->id);

            Log::info('Manual Florida transmission created', [
                'transmission_id' => $transmission->id,
                'enrollment_id' => $enrollment->id,
                'admin_user' => auth()->id(),
            ]);

            return back()->with('success', 'Manual transmission created and queued for sending');
        } catch (\Exception $e) {
            Log::error('Failed to create manual Florida transmission', [
                'enrollment_id' => $request->enrollment_id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to create manual transmission: ' . $e->getMessage());
        }
    }

    /**
     * Get error code statistics for dashboard.
     */
    public function errorStats()
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access');
        }

        $errorStats = StateTransmission::forState('FL')
            ->where('status', 'error')
            ->selectRaw('response_code, COUNT(*) as count, MAX(created_at) as latest_error')
            ->groupBy('response_code')
            ->orderBy('count', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($stat) {
                // Add human-readable error message
                $service = new FlhsmvSoapService();
                $errorInfo = $service->mapFloridaErrorCode($stat->response_code);
                
                return [
                    'code' => $stat->response_code,
                    'count' => $stat->count,
                    'latest_error' => $stat->latest_error,
                    'message' => $errorInfo['message'] ?? 'Unknown error',
                    'retryable' => $errorInfo['retryable'] ?? false,
                ];
            });

        return response()->json($errorStats);
    }

    /**
     * Export transmission data to CSV.
     */
    public function export(Request $request)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access');
        }

        $query = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->whereHas('enrollment')
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transmissions = $query->get();

        $csvData = [];
        $csvData[] = [
            'ID',
            'Student Name',
            'Email',
            'Citation Number',
            'Driver License',
            'Status',
            'Response Code',
            'Response Message',
            'Retry Count',
            'Created At',
            'Sent At',
        ];

        foreach ($transmissions as $transmission) {
            $user = $transmission->enrollment->user;
            $csvData[] = [
                $transmission->id,
                $user->first_name . ' ' . $user->last_name,
                $user->email,
                $transmission->enrollment->citation_number,
                $user->driver_license,
                $transmission->status,
                $transmission->response_code,
                $transmission->response_message,
                $transmission->retry_count,
                $transmission->created_at->format('Y-m-d H:i:s'),
                $transmission->sent_at ? $transmission->sent_at->format('Y-m-d H:i:s') : '',
            ];
        }

        $filename = 'florida_transmissions_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
