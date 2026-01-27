<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCaliforniaTransmissionJob;
use App\Models\StateTransmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CaTransmissionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('CA')
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $transmissions = $query->paginate(50);

        // Separate by status for display
        $pending = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('CA')
            ->pending()
            ->whereHas('enrollment')
            ->count();

        $errors = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('CA')
            ->error()
            ->whereHas('enrollment')
            ->count();

        $successful = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('CA')
            ->success()
            ->whereHas('enrollment')
            ->count();

        return view('admin.ca-transmissions.index', compact(
            'transmissions',
            'status',
            'pending',
            'errors',
            'successful'
        ));
    }

    public function show($id)
    {
        $transmission = StateTransmission::with(['enrollment.user', 'enrollment.course', 'enrollment.californiaCertificate'])
            ->findOrFail($id);

        return view('admin.ca-transmissions.show', compact('transmission'));
    }

    public function retry($id)
    {
        $transmission = StateTransmission::findOrFail($id);

        if ($transmission->status !== 'error') {
            return redirect()->back()->with('error', 'Only failed transmissions can be retried');
        }

        // Reset transmission
        $transmission->update([
            'status' => 'pending',
            'response_code' => null,
            'response_message' => null,
            'retry_count' => 0,
        ]);

        // Dispatch job
        SendCaliforniaTransmissionJob::dispatch($transmission->id);

        Log::info('California transmission retry initiated', [
            'transmission_id' => $transmission->id,
            'enrollment_id' => $transmission->enrollment_id,
        ]);

        return redirect()->back()->with('success', 'Transmission retry initiated');
    }

    public function sendAll()
    {
        $pendingTransmissions = StateTransmission::forState('CA')
            ->pending()
            ->get();

        $count = 0;
        foreach ($pendingTransmissions as $transmission) {
            SendCaliforniaTransmissionJob::dispatch($transmission->id);
            $count++;
        }

        Log::info("Dispatched {$count} California transmissions");

        return redirect()->back()->with('success', "Dispatched {$count} pending transmissions");
    }

    public function retryAll()
    {
        $errorTransmissions = StateTransmission::forState('CA')
            ->error()
            ->get();

        $count = 0;
        foreach ($errorTransmissions as $transmission) {
            // Reset transmission
            $transmission->update([
                'status' => 'pending',
                'response_code' => null,
                'response_message' => null,
                'retry_count' => 0,
            ]);

            SendCaliforniaTransmissionJob::dispatch($transmission->id);
            $count++;
        }

        Log::info("Retrying {$count} failed California transmissions");

        return redirect()->back()->with('success', "Retrying {$count} failed transmissions");
    }

    public function destroy($id)
    {
        $transmission = StateTransmission::findOrFail($id);

        $transmission->delete();

        return redirect()->route('admin.ca-transmissions.index')
            ->with('success', 'Transmission deleted successfully');
    }
}
