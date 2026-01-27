<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StateTransmission;
use App\Services\CaliforniaTvccService;
use App\Services\CcsService;
use App\Services\NevadaNtsaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StateTransmissionController extends Controller
{
    /**
     * Display all state transmissions with filtering.
     */
    public function index(Request $request)
    {
        if (!auth()->check() || !auth()->user()->role || !in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $query = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->whereHas('enrollment')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('system')) {
            $query->where('system', $request->system);
        }

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

        // Get statistics
        $stats = [
            'total' => StateTransmission::whereHas('enrollment')->count(),
            'pending' => StateTransmission::whereHas('enrollment')->where('status', 'pending')->count(),
            'success' => StateTransmission::whereHas('enrollment')->where('status', 'success')->count(),
            'error' => StateTransmission::whereHas('enrollment')->where('status', 'error')->count(),
            'by_state' => StateTransmission::whereHas('enrollment')
                ->selectRaw('state, `system`, count(*) as count')
                ->groupBy('state', 'system')
                ->get(),
        ];

        return view('admin.state-transmissions.index', compact('transmissions', 'stats'));
    }

    /**
     * Show transmission details.
     */
    public function show($id)
    {
        if (!auth()->check() || !auth()->user()->role || !in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->findOrFail($id);

        return view('admin.state-transmissions.show', compact('transmission'));
    }

    /**
     * Retry a failed transmission.
     */
    public function retry($id)
    {
        if (!auth()->check() || !auth()->user()->role || !in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
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

            // Send based on system
            $success = $this->sendTransmission($transmission);

            if ($success) {
                return back()->with('success', 'Transmission retry successful.');
            } else {
                return back()->with('error', 'Transmission retry failed. Check logs for details.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to retry transmission', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to retry transmission: ' . $e->getMessage());
        }
    }

    /**
     * Send a pending transmission.
     */
    public function send($id)
    {
        if (!auth()->check() || !auth()->user()->role || !in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::findOrFail($id);

        if ($transmission->status !== 'pending') {
            return back()->with('error', 'Only pending transmissions can be sent.');
        }

        try {
            $success = $this->sendTransmission($transmission);

            if ($success) {
                return back()->with('success', 'Transmission sent successfully.');
            } else {
                return back()->with('error', 'Transmission failed. Check logs for details.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send transmission', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send transmission: ' . $e->getMessage());
        }
    }

    /**
     * Send transmission based on system type.
     */
    protected function sendTransmission(StateTransmission $transmission): bool
    {
        // Handle NULL or empty system by determining from state
        $system = $transmission->system;
        if (empty($system)) {
            $system = $this->determineSystemFromState($transmission->state);
            $transmission->update(['system' => $system]);
        }

        switch ($system) {
            case 'TVCC':
                $service = new CaliforniaTvccService();
                return $service->sendTransmission($transmission);

            case 'NTSA':
                $service = new NevadaNtsaService();
                return $service->sendTransmission($transmission);

            case 'CCS':
                $service = new CcsService();
                return $service->sendTransmission($transmission);

            case 'FLHSMV':
                $job = new \App\Jobs\SendFloridaTransmissionJob($transmission->id);
                $job->handle();
                $transmission->refresh();
                return $transmission->status === 'success';

            default:
                throw new \Exception("Unknown transmission system: {$system} for state: {$transmission->state}");
        }
    }

    /**
     * Determine system from state code.
     */
    private function determineSystemFromState(string $state): string
    {
        switch ($state) {
            case 'FL':
                return 'FLHSMV';
            case 'CA':
                return 'TVCC';
            case 'NV':
                return 'NTSA';
            case 'TX':
            case 'DE':
            case 'MO':
            default:
                return 'CCS';
        }
    }

    /**
     * Delete a transmission record.
     */
    public function destroy($id)
    {
        if (!auth()->check() || !auth()->user()->role || auth()->user()->role->slug !== 'super-admin') {
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
}
