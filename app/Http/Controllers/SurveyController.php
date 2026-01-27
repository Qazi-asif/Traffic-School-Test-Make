<?php

namespace App\Http\Controllers;

use App\Models\UserCourseEnrollment;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    protected $surveyService;

    public function __construct(SurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function show(UserCourseEnrollment $enrollment)
    {
        // Ensure user owns this enrollment
        if ($enrollment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this survey.');
        }

        // Check if course is completed
        if (! $enrollment->completed) {
            return redirect()->route('course.player', $enrollment->id)
                ->with('error', 'Please complete the course before taking the survey.');
        }

        // Find applicable survey
        $survey = $this->surveyService->findApplicableSurvey($enrollment);

        if (! $survey) {
            // No survey required, redirect to certificate
            return redirect("/generate-certificate/{$enrollment->id}");
        }

        // Check if already completed
        if ($this->surveyService->hasCompletedRequiredSurvey($enrollment)) {
            return redirect()->route('survey.thank-you', $enrollment->id);
        }

        // Start or get existing response
        $response = $this->surveyService->startSurvey($survey, $enrollment);

        return view('survey.show', [
            'survey' => $survey,
            'enrollment' => $enrollment,
            'response' => $response,
            'questions' => $survey->questions,
        ]);
    }

    public function submit(Request $request, UserCourseEnrollment $enrollment)
    {
        // Ensure user owns this enrollment
        if ($enrollment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        $survey = $this->surveyService->findApplicableSurvey($enrollment);

        if (! $survey) {
            return redirect("/generate-certificate/{$enrollment->id}");
        }

        // Validate required questions
        $rules = [];
        foreach ($survey->questions as $question) {
            if ($question->is_required) {
                $rules["question_{$question->id}"] = 'required';
            }
        }

        $validated = $request->validate($rules, [
            'required' => 'This question is required.',
        ]);

        DB::beginTransaction();
        try {
            $response = $this->surveyService->startSurvey($survey, $enrollment);

            // Save all answers
            foreach ($survey->questions as $question) {
                $answer = $request->input("question_{$question->id}");
                if ($answer !== null) {
                    $this->surveyService->saveAnswer($response, $question, $answer);
                }
            }

            // Mark as complete
            $this->surveyService->completeSurvey($response);

            DB::commit();

            return redirect()->route('survey.thank-you', $enrollment->id);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to submit survey. Please try again.');
        }
    }

    public function thankYou(UserCourseEnrollment $enrollment)
    {
        if ($enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        return view('survey.thank-you', [
            'enrollment' => $enrollment,
        ]);
    }
}
