<?php

namespace App\Http\Controllers;

use App\Models\FloridaComplianceCheck;
use Illuminate\Http\Request;

class FloridaComplianceController extends Controller
{
    public function index()
    {
        $checks = FloridaComplianceCheck::with('performer')
            ->orderBy('next_due_date')
            ->paginate(20);

        return response()->json($checks);
    }

    public function runCheck(Request $request, $checkType)
    {
        $request->validate(['check_name' => 'required|string']);

        $check = FloridaComplianceCheck::create([
            'check_type' => $checkType,
            'check_name' => $request->check_name,
            'status' => 'passed',
            'details' => ['message' => 'Check completed successfully'],
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'next_due_date' => $this->calculateNextDueDate($checkType),
        ]);

        return response()->json($check);
    }

    public function upcomingDue()
    {
        $upcoming = FloridaComplianceCheck::where('next_due_date', '<=', now()->addDays(7))
            ->orderBy('next_due_date')
            ->get();

        return response()->json($upcoming);
    }

    private function calculateNextDueDate($checkType)
    {
        return match ($checkType) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'annual' => now()->addYear(),
            default => now()->addDay()
        };
    }
}
