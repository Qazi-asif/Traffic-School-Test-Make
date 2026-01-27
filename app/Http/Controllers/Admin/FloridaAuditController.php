<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FloridaCourse;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;

class FloridaAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->whereHas('floridaCourse');

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderBy('created_at', 'desc')->paginate(50);
        $courses = FloridaCourse::all();

        return view('admin.florida-audit.index', compact('enrollments', 'courses'));
    }

    public function export(Request $request)
    {
        $query = UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->whereHas('floridaCourse');

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->get();

        $filename = 'florida_audit_'.now()->format('Y_m_d_H_i_s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($enrollments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['User Name', 'Email', 'Course Title', 'Status', 'Enrolled Date', 'Completed Date', 'Progress']);

            foreach ($enrollments as $enrollment) {
                fputcsv($file, [
                    $enrollment->user->name,
                    $enrollment->user->email,
                    $enrollment->floridaCourse->title,
                    $enrollment->status,
                    $enrollment->created_at->format('Y-m-d H:i:s'),
                    $enrollment->completed_at ? $enrollment->completed_at->format('Y-m-d H:i:s') : '',
                    $enrollment->progress.'%',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
