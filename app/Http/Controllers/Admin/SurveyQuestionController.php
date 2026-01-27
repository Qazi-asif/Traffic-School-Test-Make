<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

class SurveyQuestionController extends Controller
{
    public function index(Survey $survey)
    {
        $questions = $survey->questions()->orderBy('display_order')->get();

        return response()->json($questions);
    }

    public function store(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:rating,multiple_choice,text,yes_no,scale_1_5,scale_1_10',
            'options_text' => 'nullable|string',
            'is_required' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        // Convert options_text to array if provided
        if ($request->filled('options_text')) {
            $validated['options'] = array_filter(array_map('trim', explode("\n", $request->options_text)));
        }
        unset($validated['options_text']);

        $validated['survey_id'] = $survey->id;
        $question = SurveyQuestion::create($validated);

        return back()->with('success', 'Question added successfully.');
    }

    public function update(Request $request, Survey $survey, SurveyQuestion $question)
    {
        if ($question->survey_id !== $survey->id) {
            abort(404);
        }

        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:rating,multiple_choice,text,yes_no,scale_1_5,scale_1_10',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        $question->update($validated);

        return back()->with('success', 'Question updated successfully.');
    }

    public function destroy(Survey $survey, SurveyQuestion $question)
    {
        if ($question->survey_id !== $survey->id) {
            abort(404);
        }

        $question->delete();

        return back()->with('success', 'Question deleted successfully.');
    }

    public function reorder(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*' => 'exists:survey_questions,id',
        ]);

        foreach ($validated['questions'] as $order => $questionId) {
            SurveyQuestion::where('id', $questionId)
                ->where('survey_id', $survey->id)
                ->update(['display_order' => $order]);
        }

        return response()->json(['success' => true]);
    }
}
