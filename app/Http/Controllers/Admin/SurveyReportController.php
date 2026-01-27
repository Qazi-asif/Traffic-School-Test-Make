<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Survey;
use App\Services\SurveyService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SurveyReportController extends Controller
{
    protected $surveyService;

    public function __construct(SurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function index(Request $request)
    {
        $surveys = Survey::active()
            ->withCount(['responses', 'questions'])
            ->get();

        $totalResponses = $surveys->sum('responses_count');
        $activeSurveys = $surveys->count();

        return view('admin.survey-reports.index', compact('surveys', 'totalResponses', 'activeSurveys'));
    }

    public function bySurvey(Survey $survey, Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? Carbon::parse($request->to) : null;

        $statistics = $this->surveyService->generateStatistics($survey, $from, $to);

        return view('admin.survey-reports.by-survey', compact('statistics', 'survey'));
    }

    public function byState(Request $request, string $stateCode)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? Carbon::parse($request->to) : null;

        $report = $this->surveyService->generateStateReport($stateCode, $from, $to);

        return view('admin.survey-reports.by-state', compact('report', 'stateCode'));
    }

    public function byCourse(Course $course, Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? Carbon::parse($request->to) : null;

        $surveys = Survey::active()
            ->where('course_id', $course->id)
            ->with('questions')
            ->get();

        $reports = [];
        foreach ($surveys as $survey) {
            $reports[] = $this->surveyService->generateStatistics($survey, $from, $to);
        }

        return view('admin.survey-reports.by-course', compact('reports', 'course'));
    }

    public function byDateRange(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'survey_id' => 'nullable|exists:surveys,id',
        ]);

        $from = Carbon::parse($validated['from']);
        $to = Carbon::parse($validated['to']);

        if ($request->filled('survey_id')) {
            $survey = Survey::findOrFail($validated['survey_id']);

            return redirect()->route('admin.survey-reports.by-survey', [
                'survey' => $survey,
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ]);
        }

        $surveys = Survey::active()->with('questions')->get();
        $reports = [];

        foreach ($surveys as $survey) {
            $reports[] = $this->surveyService->generateStatistics($survey, $from, $to);
        }

        return view('admin.survey-reports.date-range', compact('reports', 'from', 'to'));
    }

    public function print(Survey $survey, Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? Carbon::parse($request->to) : null;

        $statistics = $this->surveyService->generateStatistics($survey, $from, $to);

        return view('admin.survey-reports.print', compact('statistics', 'survey'));
    }

    public function delaware(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? Carbon::parse($request->to) : null;

        $report = $this->surveyService->generateStateReport('DE', $from, $to);

        return view('admin.survey-reports.delaware', compact('report'));
    }

    public function export(Survey $survey, string $type, Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? Carbon::parse($request->to) : null;

        $statistics = $this->surveyService->generateStatistics($survey, $from, $to);

        if ($type === 'pdf') {
            $pdf = \PDF::loadView('admin.survey-reports.pdf', compact('statistics', 'survey'));

            return $pdf->download("survey_report_{$survey->id}.pdf");
        }

        if ($type === 'csv') {
            $data = $this->surveyService->exportResponses($survey, 'csv');
            $filename = "survey_{$survey->id}_report_".now()->format('Y-m-d').'.csv';

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);
        }

        return back()->with('error', 'Invalid export type.');
    }
}
