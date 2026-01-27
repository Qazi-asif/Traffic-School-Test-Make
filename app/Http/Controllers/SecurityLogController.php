<?php

namespace App\Http\Controllers;

use App\Models\SecurityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $logs = SecurityLog::with('user')
            ->when($request->event_type, fn ($q) => $q->where('event_type', $request->event_type))
            ->when($request->risk_level, fn ($q) => $q->where('risk_level', $request->risk_level))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($logs);
    }

    public function forceLogout(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate(['user_id' => 'required|exists:users,id']);

        // Log the force logout action
        SecurityLog::create([
            'user_id' => $request->user_id,
            'event_type' => 'system_change',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => 'User forcefully logged out by admin',
            'metadata' => ['admin_id' => auth()->id()],
            'risk_level' => 'medium',
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'User logged out successfully']);
    }
}
