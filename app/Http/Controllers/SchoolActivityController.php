<?php

namespace App\Http\Controllers;

use App\Models\FloridaCertificate;
use App\Models\SchoolActivityReport;
use Illuminate\Http\Request;

class SchoolActivityController extends Controller
{
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'date_range_start' => 'required|date',
            'date_range_end' => 'required|date|after:date_range_start',
            'school_id' => 'required|exists:florida_schools,id',
            'course_type' => 'required|in:BDI,ADI,TLSAE',
        ]);

        $certificatesIssued = FloridaCertificate::where('school_id', $validated['school_id'])
            ->where('course_type', $validated['course_type'])
            ->whereBetween('issue_date', [$validated['date_range_start'], $validated['date_range_end']])
            ->count();

        $report = SchoolActivityReport::create([
            'report_date' => now(),
            'date_range_start' => $validated['date_range_start'],
            'date_range_end' => $validated['date_range_end'],
            'school_id' => $validated['school_id'],
            'course_type' => $validated['course_type'],
            'certificates_issued' => $certificatesIssued,
            'generated_by' => auth()->id(),
            'report_data' => ['summary' => 'Activity report generated'],
        ]);

        return response()->json($report, 201);
    }

    public function index()
    {
        return response()->json(SchoolActivityReport::with('school')->latest()->get());
    }

    public function show($id)
    {
        return response()->json(SchoolActivityReport::with('school')->findOrFail($id));
    }
}
