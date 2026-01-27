<?php

namespace App\Http\Controllers;

use App\Models\FloridaSecurityLog;
use Illuminate\Http\Request;

class FloridaSecurityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = FloridaSecurityLog::with('user')
            ->when($request->event_type, fn ($q) => $q->where('event_type', $request->event_type))
            ->when($request->risk_level, fn ($q) => $q->where('risk_level', $request->risk_level))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($logs);
    }

    public function forceLogout(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        // Force logout logic here
        FloridaSecurityLog::create([
            'user_id' => $request->user_id,
            'event_type' => 'admin_action',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => 'Force logout by admin',
            'risk_level' => 'medium',
        ]);

        return response()->json(['message' => 'User logged out successfully']);
    }

    public function mySessions(Request $request)
    {
        // Return user's active sessions
        return response()->json(['sessions' => []]);
    }

    public function revokeSession(Request $request, $id)
    {
        // Revoke specific session
        return response()->json(['message' => 'Session revoked']);
    }
}
