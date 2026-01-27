<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Jobs\SendFloridaTransmissionJob;
use App\Models\StateTransmission;
use App\Services\CaliforniaTvccService;
use App\Services\CcsService;
use App\Services\NevadaNtsaService;
use Illuminate\Support\Facades\Log;

class CreateStateTransmission
{
    /**
     * Handle the event - creates and sends state transmissions synchronously or queued.
     */
    public function handle(CourseCompleted $event): void
    {
        $enrollment = $event->enrollment;
        $enrollment->load(['user', 'course']);
        
        $course = $enrollment->course;
        
        // Get state from either 'state' or 'state_code' column
        $courseState = $course->state ?? $course->state_code ?? null;
        
        $syncMode = config('state-integrations.sync_execution', true);

        Log::info('CreateStateTransmission listener fired', [
            'enrollment_id' => $enrollment->id,
            'course_state' => $courseState,
            'course_title' => $course->title,
        ]);

        // Create transmission based on course state
        if ($courseState) {
            if ($courseState === 'FL') {
                $this->handleFloridaTransmission($enrollment, $syncMode);
            } elseif ($courseState === 'CA') {
                $this->handleCaliforniaTvcc($enrollment, $syncMode);
            } elseif ($courseState === 'NV') {
                $this->handleNevadaNtsa($enrollment, $syncMode);
            } else {
                // All other states use CCS
                $this->handleCcs($enrollment, $syncMode);
            }
        } else {
            Log::warning('Course has no state set', ['enrollment_id' => $enrollment->id, 'course_id' => $course->id]);
        }
    }

    protected function handleFloridaTransmission($enrollment, bool $syncMode): void
    {
        try {
            $transmission = StateTransmission::create([
                'enrollment_id' => $enrollment->id,
                'state' => 'FL',
                'system' => 'FLHSMV',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            if ($syncMode) {
                // Execute synchronously
                $job = new SendFloridaTransmissionJob($transmission->id);
                $job->handle();
            } else {
                // Queue for background processing
                SendFloridaTransmissionJob::dispatch($transmission->id);
            }

            Log::info('Florida transmission created', [
                'transmission_id' => $transmission->id,
                'enrollment_id' => $enrollment->id,
                'sync' => $syncMode,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Florida transmission', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleCaliforniaTvcc($enrollment, bool $syncMode): void
    {
        try {
            $transmission = StateTransmission::create([
                'enrollment_id' => $enrollment->id,
                'state' => 'CA',
                'system' => 'TVCC',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            if ($syncMode) {
                $service = new CaliforniaTvccService();
                $service->sendTransmission($transmission);
            }

            Log::info('California TVCC transmission created', [
                'transmission_id' => $transmission->id,
                'enrollment_id' => $enrollment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create California TVCC transmission', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleNevadaNtsa($enrollment, bool $syncMode): void
    {
        try {
            $transmission = StateTransmission::create([
                'enrollment_id' => $enrollment->id,
                'state' => 'NV',
                'system' => 'NTSA',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            if ($syncMode) {
                $service = new NevadaNtsaService();
                $service->sendTransmission($transmission);
            }

            Log::info('Nevada NTSA transmission created', [
                'transmission_id' => $transmission->id,
                'enrollment_id' => $enrollment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Nevada NTSA transmission', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleCcs($enrollment, bool $syncMode): void
    {
        try {
            $course = $enrollment->course;
            $courseState = $course->state ?? $course->state_code ?? null;
            
            $transmission = StateTransmission::create([
                'enrollment_id' => $enrollment->id,
                'state' => $courseState,
                'system' => 'CCS',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            if ($syncMode) {
                $service = new CcsService();
                $service->sendTransmission($transmission);
            }

            Log::info('CCS transmission created', [
                'transmission_id' => $transmission->id,
                'enrollment_id' => $enrollment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create CCS transmission', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
