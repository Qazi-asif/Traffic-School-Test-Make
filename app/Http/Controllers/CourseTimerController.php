<?php

namespace App\Http\Controllers;

use App\Models\CourseTimer;
use App\Services\CourseTimerService;
use Illuminate\Http\Request;

class CourseTimerController extends Controller
{
    protected $timerService;

    public function __construct(CourseTimerService $timerService)
    {
        $this->timerService = $timerService;
    }

    public function startTimer(Request $request)
    {
        \Log::info('=== Timer Start Request ===');
        \Log::info('User authenticated: ' . (auth()->check() ? 'YES' : 'NO'));
        \Log::info('User ID: ' . (auth()->id() ?? 'NULL'));
        \Log::info('User email: ' . (auth()->user()->email ?? 'NULL'));
        \Log::info('Auth guard: ' . auth()->getDefaultDriver());
        \Log::info('Request data: ', $request->all());
        \Log::info('Request headers: ', $request->headers->all());
        \Log::info('Session ID: ' . session()->getId());
        \Log::info('CSRF Token: ' . csrf_token());

        if (!auth()->check()) {
            \Log::error('Authentication failed - user not logged in');
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $request->validate([
            'chapter_id' => 'required|integer',
            'chapter_type' => 'nullable|string|in:chapters,course_chapters',
            'browser_fingerprint' => 'required|string'
        ]);

        $chapterType = $request->chapter_type ?? 'chapters';
        $result = $this->timerService->startTimer(
            auth()->id(), 
            $request->chapter_id, 
            $chapterType
        );

        \Log::info('Timer start result: ', $result);
        return response()->json($result);
    }

    public function updateTimer(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:timer_sessions,id',
            'time_spent' => 'required|integer|min:0',
            'session_token' => 'nullable|string',
            'browser_fingerprint' => 'required|string',
            'violations' => 'nullable|array'
        ]);

        $result = $this->timerService->updateTimer(
            $request->session_id, 
            $request->time_spent,
            $request->session_token,
            $request->violations ?? []
        );

        return response()->json($result);
    }

    public function heartbeat(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:timer_sessions,id',
            'session_token' => 'required|string',
            'timestamp' => 'required|integer'
        ]);

        $session = \App\Models\TimerSession::find($request->session_id);
        
        if (!$session || $session->session_token !== $request->session_token) {
            return response()->json(['error' => 'Invalid session'], 403);
        }

        $session->update(['last_heartbeat' => now()]);

        return response()->json(['success' => true]);
    }

    public function validateSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:timer_sessions,id',
            'session_token' => 'required|string',
            'browser_fingerprint' => 'required|string'
        ]);

        $result = $this->timerService->validateSession(
            $request->session_id,
            $request->session_token,
            $request->browser_fingerprint
        );

        return response()->json($result);
    }

    public function recordViolation(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:timer_sessions,id',
            'violation_type' => 'required|string',
            'details' => 'nullable|array'
        ]);

        $this->timerService->recordViolation(
            $request->session_id,
            $request->violation_type,
            $request->details ?? []
        );

        return response()->json(['success' => true]);
    }

    public function bypassTimer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        // Only admins can bypass
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->timerService->bypassTimer(
            $request->user_id,
            $request->chapter_id,
            auth()->id()
        );

        return response()->json($result);
    }

    public function checkTimerStatus(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $isCompleted = $this->timerService->isTimerCompleted(auth()->id(), $request->chapter_id);

        return response()->json(['completed' => $isCompleted]);
    }

    public function configureTimer(Request $request)
    {
        try {
            \Log::info('=== Configure Timer Request START ===');
            \Log::info('Request data:', $request->all());
            \Log::info('Request headers:', $request->headers->all());

            $request->validate([
                'chapter_id' => 'required|integer',
                'chapter_type' => 'nullable|string|in:chapters,course_chapters',
                'required_time_minutes' => 'required|integer|min:1',
                'is_enabled' => 'boolean',
                'allow_pause' => 'boolean',
                'bypass_for_admin' => 'boolean',
            ]);

            \Log::info('Validation passed');

            $data = [
                'chapter_id' => $request->chapter_id,
                'chapter_type' => $request->chapter_type ?? 'chapters',
                'required_time_minutes' => $request->required_time_minutes,
                'is_enabled' => $request->is_enabled ?? true,
                'allow_pause' => $request->allow_pause ?? true,
                'bypass_for_admin' => $request->bypass_for_admin ?? true,
            ];

            \Log::info('Data to save:', $data);

            $timer = CourseTimer::updateOrCreate(
                [
                    'chapter_id' => $data['chapter_id'],
                    'chapter_type' => $data['chapter_type'],
                ],
                [
                    'required_time_minutes' => $data['required_time_minutes'],
                    'is_enabled' => $data['is_enabled'],
                    'allow_pause' => $data['allow_pause'],
                    'bypass_for_admin' => $data['bypass_for_admin'],
                ]
            );

            \Log::info('Timer saved successfully:', $timer->toArray());
            \Log::info('=== Configure Timer Request END ===');

            return response()->json(['success' => true, 'timer' => $timer]);
        } catch (\Exception $e) {
            \Log::error('=== Configure Timer ERROR ===');
            \Log::error('Error message: '.$e->getMessage());
            \Log::error('Error file: '.$e->getFile().':'.$e->getLine());
            \Log::error('Stack trace: '.$e->getTraceAsString());
            \Log::error('=== Configure Timer ERROR END ===');

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
