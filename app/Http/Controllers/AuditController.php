<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\SecurityLog;
use Illuminate\Http\JsonResponse;

class AuditController extends Controller
{
    public function getDashboard(): JsonResponse
    {
        try {
            if (! auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $stats = [
                'total_events' => SecurityLog::count(),
                'failed_logins_today' => LoginAttempt::where('successful', false)
                    ->whereDate('attempted_at', today())
                    ->count(),
                'high_risk_events' => SecurityLog::where('risk_level', 'high')->count(),
                'critical_events' => SecurityLog::where('risk_level', 'critical')->count(),
            ];

            $recentEvents = SecurityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'stats' => $stats,
                'recent_events' => $recentEvents,
            ]);
        } catch (\Exception $e) {
            \Log::error('Audit dashboard failed: '.$e->getMessage());

            return response()->json([
                'stats' => [
                    'total_events' => 0,
                    'failed_logins_today' => 0,
                    'high_risk_events' => 0,
                    'critical_events' => 0,
                ],
                'recent_events' => [],
                'error' => 'Failed to load dashboard data',
            ], 500);
        }
    }

    public function getComplianceReport(): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $report = [
            'gdpr_requests' => \App\Models\DataExportRequest::where('request_type', 'gdpr')->count(),
            'ccpa_requests' => \App\Models\DataExportRequest::where('request_type', 'ccpa')->count(),
            'password_changes' => SecurityLog::where('event_type', 'password_change')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->count(),
            'data_access_events' => SecurityLog::where('event_type', 'data_access')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->count(),
        ];

        return response()->json($report);
    }
}
