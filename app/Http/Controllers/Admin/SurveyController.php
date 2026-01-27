<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = Survey::with(['course', 'questions'])
            ->withCount(['responses', 'questions']);

        if ($request->filled('state')) {
            $query->where('state_code', $request->state);
        }

        if ($request->filled('course')) {
            $query->where('course_id', $request->course);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $surveys = $query->orderBy('display_order')->paginate(20);
        $courses = Course::orderBy('title')->get();

        return view('admin.surveys.index', compact('surveys', 'courses'));
    }

    public function create()
    {
        // Get courses from both tables
        $regularCourses = Course::select('id', 'title', 'state')
            ->selectRaw("'courses' as table_type")
            ->orderBy('title')
            ->get();

        $floridaCourses = \App\Models\FloridaCourse::select('id', 'title', 'state_code as state')
            ->selectRaw("'florida_courses' as table_type")
            ->orderBy('title')
            ->get();

        // Combine courses with clear labels
        $courses = collect();
        
        foreach ($regularCourses as $course) {
            $course->display_title = $course->title . ' (' . ($course->state ?? 'No State') . ') - Regular';
            $courses->push($course);
        }
        
        foreach ($floridaCourses as $course) {
            $course->id = 'florida_' . $course->id; // Prefix to distinguish
            $course->display_title = $course->title . ' (' . ($course->state ?? 'FL') . ') - Florida';
            $courses->push($course);
        }

        $states = ['FL' => 'Florida', 'MO' => 'Missouri', 'TX' => 'Texas', 'DE' => 'Delaware'];

        return view('admin.surveys.create', compact('courses', 'states'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'state_code' => 'nullable|string|size:2',
            'course_id' => 'nullable|string', // Allow string to handle florida_ prefixes
            'is_active' => 'boolean',
            'is_required' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        // Handle course_id parsing for Florida courses
        if ($validated['course_id'] && strpos($validated['course_id'], 'florida_') === 0) {
            // Extract the actual Florida course ID
            $actualCourseId = (int) str_replace('florida_', '', $validated['course_id']);
            
            // Verify the Florida course exists
            $floridaCourse = \App\Models\FloridaCourse::find($actualCourseId);
            if (!$floridaCourse) {
                return back()->withErrors(['course_id' => 'Selected Florida course not found.']);
            }
            
            // Store the actual course ID and mark it as Florida course
            $validated['course_id'] = $actualCourseId;
            $validated['course_table'] = 'florida_courses';
        } else {
            // Regular course - verify it exists if provided
            if ($validated['course_id']) {
                $regularCourse = Course::find($validated['course_id']);
                if (!$regularCourse) {
                    return back()->withErrors(['course_id' => 'Selected course not found.']);
                }
            }
            $validated['course_table'] = 'courses';
        }

        $survey = Survey::create($validated);

        return redirect()->route('admin.surveys.show', $survey)
            ->with('success', 'Survey created successfully. Now add questions.');
    }

    public function show(Survey $survey)
    {
        $survey->load(['questions' => fn ($q) => $q->orderBy('display_order'), 'course']);
        $responsesCount = $survey->responses()->count();
        $completedCount = $survey->responses()->completed()->count();

        return view('admin.surveys.show', compact('survey', 'responsesCount', 'completedCount'));
    }

    public function edit(Survey $survey)
    {
        // Get courses from both tables
        $regularCourses = Course::select('id', 'title', 'state')
            ->selectRaw("'courses' as table_type")
            ->orderBy('title')
            ->get();

        $floridaCourses = \App\Models\FloridaCourse::select('id', 'title', 'state_code as state')
            ->selectRaw("'florida_courses' as table_type")
            ->orderBy('title')
            ->get();

        // Combine courses with clear labels
        $courses = collect();
        
        foreach ($regularCourses as $course) {
            $course->display_title = $course->title . ' (' . ($course->state ?? 'No State') . ') - Regular';
            $courses->push($course);
        }
        
        foreach ($floridaCourses as $course) {
            $course->id = 'florida_' . $course->id; // Prefix to distinguish
            $course->display_title = $course->title . ' (' . ($course->state ?? 'FL') . ') - Florida';
            $courses->push($course);
        }

        $states = ['FL' => 'Florida', 'MO' => 'Missouri', 'TX' => 'Texas', 'DE' => 'Delaware'];

        return view('admin.surveys.edit', compact('survey', 'courses', 'states'));
    }

    public function update(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'state_code' => 'nullable|string|size:2',
            'course_id' => 'nullable|string', // Allow string to handle florida_ prefixes
            'is_active' => 'boolean',
            'is_required' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        // Handle course_id parsing for Florida courses
        if ($validated['course_id'] && strpos($validated['course_id'], 'florida_') === 0) {
            // Extract the actual Florida course ID
            $actualCourseId = (int) str_replace('florida_', '', $validated['course_id']);
            
            // Verify the Florida course exists
            $floridaCourse = \App\Models\FloridaCourse::find($actualCourseId);
            if (!$floridaCourse) {
                return back()->withErrors(['course_id' => 'Selected Florida course not found.']);
            }
            
            // Store the actual course ID and mark it as Florida course
            $validated['course_id'] = $actualCourseId;
            $validated['course_table'] = 'florida_courses';
        } else {
            // Regular course - verify it exists if provided
            if ($validated['course_id']) {
                $regularCourse = Course::find($validated['course_id']);
                if (!$regularCourse) {
                    return back()->withErrors(['course_id' => 'Selected course not found.']);
                }
            }
            $validated['course_table'] = 'courses';
        }

        $survey->update($validated);

        return redirect()->route('admin.surveys.show', $survey)
            ->with('success', 'Survey updated successfully.');
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();

        return redirect()->route('admin.surveys.index')
            ->with('success', 'Survey deleted successfully.');
    }

    public function duplicate(Survey $survey)
    {
        $newSurvey = $survey->replicate();
        $newSurvey->name = $survey->name.' (Copy)';
        $newSurvey->is_active = false;
        $newSurvey->save();

        foreach ($survey->questions as $question) {
            $newQuestion = $question->replicate();
            $newQuestion->survey_id = $newSurvey->id;
            $newQuestion->save();
        }

        return redirect()->route('admin.surveys.show', $newSurvey)
            ->with('success', 'Survey duplicated successfully.');
    }

    public function toggleActive(Survey $survey)
    {
        $survey->update(['is_active' => ! $survey->is_active]);

        return back()->with('success', 'Survey status updated.');
    }

    public function responses(Survey $survey)
    {
        $responses = $survey->responses()
            ->with(['user', 'enrollment.course'])
            ->completed()
            ->latest('completed_at')
            ->paginate(50);

        return view('admin.surveys.responses', compact('survey', 'responses'));
    }

    public function export(Survey $survey, Request $request)
    {
        $surveyService = app(\App\Services\SurveyService::class);
        $data = $surveyService->exportResponses($survey, 'csv');

        $filename = "survey_{$survey->id}_responses_".now()->format('Y-m-d').'.csv';

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
}
