<?php

namespace App\Http\Controllers;

use App\Models\FloridaAuditTrail;
use Illuminate\Http\Request;

class FloridaAuditController extends Controller
{
    public function trails(Request $request)
    {
        $trails = FloridaAuditTrail::with('user')
            ->when($request->action, fn ($q) => $q->where('action', 'like', "%{$request->action}%"))
            ->when($request->florida_required, fn ($q) => $q->where('florida_required', true))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($trails);
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $report = FloridaAuditTrail::whereBetween('created_at', [$request->start_date, $request->end_date])
            ->where('florida_required', true)
            ->get();

        return response()->json(['report' => $report]);
    }

    public function complianceStatus()
    {
        $status = [
            'total_audits' => FloridaAuditTrail::count(),
            'florida_required' => FloridaAuditTrail::where('florida_required', true)->count(),
            'last_24h' => FloridaAuditTrail::where('created_at', '>=', now()->subDay())->count(),
        ];

        return response()->json($status);
    }
}
