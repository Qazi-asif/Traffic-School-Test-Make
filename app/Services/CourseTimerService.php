<?php

namespace App\Services;

use App\Models\CourseTimer;
use App\Models\TimerSession;
use App\Models\User;
use Illuminate\Support\Str;

class CourseTimerService
{
    public function startTimer($userId, $chapterId, $chapterType = 'chapters')
    {
        $timer = CourseTimer::where('chapter_id', $chapterId)
            ->where('chapter_type', $chapterType)
            ->first();

        if (! $timer || ! $timer->is_enabled) {
            return ['success' => true, 'timer_required' => false];
        }

        $user = User::find($userId);
        if ($user && $user->role_id == 1 && $timer->bypass_for_admin) {
            return ['success' => true, 'timer_required' => false, 'bypassed' => true];
        }

        $existingSession = TimerSession::where('user_id', $userId)
            ->where('chapter_id', $chapterId)
            ->where('is_completed', false)
            ->first();

        if ($existingSession) {
            // Resume existing session - calculate elapsed time
            $elapsedSeconds = now()->diffInSeconds($existingSession->started_at);
            $existingSession->update([
                'resumed_at' => now(),
                'resume_count' => $existingSession->resume_count + 1
            ]);
            
            return [
                'success' => true,
                'session' => $existingSession,
                'required_time' => $timer->required_time_minutes * 60,
                'elapsed_time' => min($elapsedSeconds, $existingSession->time_spent_seconds),
                'strict_mode' => true,
                'session_token' => $existingSession->session_token
            ];
        }

        // Create new session with strict tracking
        $session = TimerSession::create([
            'user_id' => $userId,
            'course_timer_id' => $timer->id,
            'chapter_id' => $chapterId,
            'started_at' => now(),
            'time_spent_seconds' => 0,
            'is_completed' => false,
            'session_token' => Str::random(32),
            'browser_fingerprint' => request()->header('User-Agent'),
            'ip_address' => request()->ip(),
            'tab_switches' => 0,
            'page_reloads' => 0,
            'resume_count' => 0
        ]);

        return [
            'success' => true,
            'session' => $session,
            'required_time' => $timer->required_time_minutes * 60,
            'elapsed_time' => 0,
            'strict_mode' => true,
            'session_token' => $session->session_token
        ];
    }

    public function updateTimer($sessionId, $timeSpent, $sessionToken = null, $violations = [])
    {
        $session = TimerSession::find($sessionId);

        if (! $session) {
            return ['success' => false, 'error' => 'Session not found'];
        }

        // Verify session token for security
        if ($sessionToken && $session->session_token !== $sessionToken) {
            $this->logViolation($session, 'invalid_token', 'Invalid session token provided');
            return ['success' => false, 'error' => 'Invalid session'];
        }

        // Update violations
        if (!empty($violations)) {
            foreach ($violations as $violation) {
                $this->logViolation($session, $violation['type'], $violation['details']);
            }
        }

        // Update time spent with validation
        $previousTime = $session->time_spent_seconds;
        $timeDiff = $timeSpent - $previousTime;
        
        // Detect time manipulation (too fast progression)
        if ($timeDiff > 65) { // Allow 5 seconds buffer for network delays
            $this->logViolation($session, 'time_manipulation', "Time jumped by {$timeDiff} seconds");
            // Don't update time if manipulation detected
            $timeSpent = $previousTime + 60; // Add maximum 1 minute
        }

        $session->update([
            'time_spent_seconds' => $timeSpent,
            'last_heartbeat' => now()
        ]);

        $timer = $session->timer;
        $requiredSeconds = $timer->required_time_minutes * 60;

        if ($timeSpent >= $requiredSeconds) {
            $session->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            return ['success' => true, 'completed' => true];
        }

        return ['success' => true, 'completed' => false];
    }

    public function validateSession($sessionId, $sessionToken, $browserFingerprint)
    {
        $session = TimerSession::find($sessionId);
        
        if (!$session) {
            return ['valid' => false, 'error' => 'Session not found'];
        }

        // Check session token
        if ($session->session_token !== $sessionToken) {
            $this->logViolation($session, 'token_mismatch', 'Session token mismatch');
            return ['valid' => false, 'error' => 'Invalid session'];
        }

        // Check browser fingerprint
        if ($session->browser_fingerprint !== $browserFingerprint) {
            $this->logViolation($session, 'browser_change', 'Browser fingerprint changed');
            return ['valid' => false, 'error' => 'Browser changed'];
        }

        // Check if session is still active (not older than 24 hours)
        if ($session->started_at->diffInHours(now()) > 24) {
            $this->logViolation($session, 'session_expired', 'Session expired after 24 hours');
            return ['valid' => false, 'error' => 'Session expired'];
        }

        return ['valid' => true];
    }

    public function recordViolation($sessionId, $violationType, $details = [])
    {
        $session = TimerSession::find($sessionId);
        if ($session) {
            $this->logViolation($session, $violationType, json_encode($details));
            
            // Update violation counters
            switch ($violationType) {
                case 'tab_switch':
                    $session->increment('tab_switches');
                    break;
                case 'page_reload':
                    $session->increment('page_reloads');
                    break;
                case 'window_blur':
                    $session->increment('focus_losses', 1, ['last_blur_at' => now()]);
                    break;
            }
        }
    }

    private function logViolation($session, $type, $details)
    {
        \Log::warning("Timer violation detected", [
            'session_id' => $session->id,
            'user_id' => $session->user_id,
            'chapter_id' => $session->chapter_id,
            'violation_type' => $type,
            'details' => $details,
            'timestamp' => now()
        ]);

        // Store violation in database
        \DB::table('timer_violations')->insert([
            'timer_session_id' => $session->id,
            'violation_type' => $type,
            'details' => $details,
            'detected_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function bypassTimer($userId, $chapterId, $adminId, $chapterType = 'chapters')
    {
        $session = TimerSession::where('user_id', $userId)
            ->where('chapter_id', $chapterId)
            ->first();

        if ($session) {
            $session->update([
                'is_completed' => true,
                'completed_at' => now(),
                'bypassed_by_admin' => true,
                'bypassed_by_user_id' => $adminId
            ]);
        } else {
            $timer = CourseTimer::where('chapter_id', $chapterId)
                ->where('chapter_type', $chapterType)
                ->first();
            $session = TimerSession::create([
                'user_id' => $userId,
                'course_timer_id' => $timer->id,
                'chapter_id' => $chapterId,
                'started_at' => now(),
                'completed_at' => now(),
                'time_spent_seconds' => 0,
                'is_completed' => true,
                'bypassed_by_admin' => true,
                'bypassed_by_user_id' => $adminId,
                'session_token' => Str::random(32)
            ]);
        }

        return ['success' => true, 'session' => $session];
    }

    public function isTimerCompleted($userId, $chapterId)
    {
        $session = TimerSession::where('user_id', $userId)
            ->where('chapter_id', $chapterId)
            ->where('is_completed', true)
            ->first();

        return $session !== null;
    }

    public function getSessionViolations($sessionId)
    {
        return \DB::table('timer_violations')
            ->where('timer_session_id', $sessionId)
            ->orderBy('detected_at', 'desc')
            ->get();
    }
}
