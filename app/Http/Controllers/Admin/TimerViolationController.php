<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimerSession;
use App\Models\TimerViolation;
use Illuminate\Http\Request;

class TimerViolationController extends Controller
{
    public function index(Request $request)
    {
        $query = TimerViolation::with(['timerSession.user', 'timerSession.timer'])
            ->orderBy('detected_at', 'desc');

        // Filter by violation type
        if ($request->violation_type) {
            $query->where('violation_type', $request->violation_type);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('detected_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('detected_at', '<=', $request->date_to);
        }

        // Filter by user
        if ($request->user_id) {
            $query->whereHas('timerSession', function($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        $violations = $query->paginate(50);

        // Get violation type counts for filter
        $violationTypes = TimerViolation::selectRaw('violation_type, COUNT(*) as count')
            ->groupBy('violation_type')
            ->orderBy('count', 'desc')
            ->get();

        return view('admin.timer-violations.index', compact('violations', 'violationTypes'));
    }

    public function show($id)
    {
        $violation = TimerViolation::with(['timerSession.user', 'timerSession.timer'])
            ->findOrFail($id);

        // Get all violations for this session
        $sessionViolations = TimerViolation::where('timer_session_id', $violation->timer_session_id)
            ->orderBy('detected_at', 'asc')
            ->get();

        return view('admin.timer-violations.show', compact('violation', 'sessionViolations'));
    }

    public function sessionViolations($sessionId)
    {
        $session = TimerSession::with(['user', 'timer', 'violations'])
            ->findOrFail($sessionId);

        return view('admin.timer-violations.session', compact('session'));
    }

    public function stats()
    {
        $stats = [
            'total_violations' => TimerViolation::count(),
            'today_violations' => TimerViolation::whereDate('detected_at', today())->count(),
            'this_week_violations' => TimerViolation::whereBetween('detected_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'violation_types' => TimerViolation::selectRaw('violation_type, COUNT(*) as count')
                ->groupBy('violation_type')
                ->orderBy('count', 'desc')
                ->get(),
            'top_violators' => TimerSession::withCount('violations')
                ->with('user')
                ->having('violations_count', '>', 0)
                ->orderBy('violations_count', 'desc')
                ->limit(10)
                ->get(),
            'recent_violations' => TimerViolation::with(['timerSession.user'])
                ->orderBy('detected_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return view('admin.timer-violations.stats', compact('stats'));
    }
}