<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\UserCourseEnrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SurveyService
{
    public function findApplicableSurvey(UserCourseEnrollment $enrollment): ?Survey
    {
        return Survey::getApplicableSurvey($enrollment);
    }

    public function hasCompletedRequiredSurvey(UserCourseEnrollment $enrollment): bool
    {
        $survey = $this->findApplicableSurvey($enrollment);

        if (! $survey || ! $survey->is_required) {
            return true; // No required survey
        }

        return SurveyResponse::where('enrollment_id', $enrollment->id)
            ->where('survey_id', $survey->id)
            ->whereNotNull('completed_at')
            ->exists();
    }

    public function startSurvey(Survey $survey, UserCourseEnrollment $enrollment): SurveyResponse
    {
        return SurveyResponse::firstOrCreate(
            [
                'survey_id' => $survey->id,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'user_id' => $enrollment->user_id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );
    }

    public function saveAnswer(SurveyResponse $response, SurveyQuestion $question, $answer): SurveyAnswer
    {
        $data = [
            'survey_response_id' => $response->id,
            'survey_question_id' => $question->id,
        ];

        // Determine which field to populate based on question type
        switch ($question->question_type) {
            case 'rating':
            case 'scale_1_5':
            case 'scale_1_10':
                $data['answer_rating'] = $answer;
                break;
            case 'multiple_choice':
            case 'yes_no':
                $data['answer_option'] = $answer;
                break;
            case 'text':
                $data['answer_text'] = $answer;
                break;
        }

        return SurveyAnswer::updateOrCreate(
            [
                'survey_response_id' => $response->id,
                'survey_question_id' => $question->id,
            ],
            $data
        );
    }

    public function completeSurvey(SurveyResponse $response): void
    {
        $response->markAsComplete();

        // Fire event
        event(new \App\Events\SurveyCompleted($response));
    }

    public function generateStatistics(Survey $survey, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = $survey->responses()->completed();

        if ($from) {
            $query->where('completed_at', '>=', $from);
        }
        if ($to) {
            $query->where('completed_at', '<=', $to);
        }

        $totalResponses = $query->count();
        $questions = $survey->questions;

        $statistics = [
            'survey' => $survey,
            'total_responses' => $totalResponses,
            'date_range' => [
                'from' => $from?->format('Y-m-d'),
                'to' => $to?->format('Y-m-d'),
            ],
            'questions' => [],
        ];

        foreach ($questions as $question) {
            $questionStats = $this->generateQuestionStatistics($question, $from, $to);
            $statistics['questions'][] = $questionStats;
        }

        return $statistics;
    }

    protected function generateQuestionStatistics(SurveyQuestion $question, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = $question->answers()
            ->whereHas('surveyResponse', function ($q) use ($from, $to) {
                $q->whereNotNull('completed_at');
                if ($from) {
                    $q->where('completed_at', '>=', $from);
                }
                if ($to) {
                    $q->where('completed_at', '<=', $to);
                }
            });

        $stats = [
            'question' => $question,
            'total_answers' => $query->count(),
        ];

        switch ($question->question_type) {
            case 'rating':
            case 'scale_1_5':
            case 'scale_1_10':
                $stats['average'] = round($query->avg('answer_rating'), 2);
                $stats['distribution'] = $query
                    ->select('answer_rating', DB::raw('count(*) as count'))
                    ->groupBy('answer_rating')
                    ->orderBy('answer_rating')
                    ->get()
                    ->pluck('count', 'answer_rating')
                    ->toArray();
                break;

            case 'multiple_choice':
            case 'yes_no':
                $stats['distribution'] = $query
                    ->select('answer_option', DB::raw('count(*) as count'))
                    ->groupBy('answer_option')
                    ->orderByDesc('count')
                    ->get()
                    ->pluck('count', 'answer_option')
                    ->toArray();
                break;

            case 'text':
                $stats['answers'] = $query
                    ->with('surveyResponse.user')
                    ->get()
                    ->map(fn ($answer) => [
                        'text' => $answer->answer_text,
                        'user' => $answer->surveyResponse->user->name ?? 'Anonymous',
                        'date' => $answer->created_at->format('Y-m-d H:i'),
                    ])
                    ->toArray();
                break;
        }

        return $stats;
    }

    public function generateStateReport(string $stateCode, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $surveys = Survey::active()
            ->where('state_code', $stateCode)
            ->with('questions')
            ->get();

        $report = [
            'state_code' => $stateCode,
            'date_range' => [
                'from' => $from?->format('Y-m-d'),
                'to' => $to?->format('Y-m-d'),
            ],
            'surveys' => [],
        ];

        foreach ($surveys as $survey) {
            $report['surveys'][] = $this->generateStatistics($survey, $from, $to);
        }

        return $report;
    }

    public function exportResponses(Survey $survey, string $format = 'csv'): array
    {
        $responses = $survey->responses()
            ->completed()
            ->with(['user', 'enrollment.course', 'answers.surveyQuestion'])
            ->get();

        $export = [];
        $headers = ['Response ID', 'User', 'Email', 'Course', 'Completed At'];

        // Add question headers
        $questions = $survey->questions;
        foreach ($questions as $question) {
            $headers[] = substr($question->question_text, 0, 50);
        }

        $export[] = $headers;

        foreach ($responses as $response) {
            $row = [
                $response->id,
                $response->user->name ?? 'N/A',
                $response->user->email ?? 'N/A',
                $response->enrollment->course->title ?? 'N/A',
                $response->completed_at->format('Y-m-d H:i:s'),
            ];

            foreach ($questions as $question) {
                $answer = $response->answers->firstWhere('survey_question_id', $question->id);
                $row[] = $answer ? $answer->getFormattedAnswer() : 'N/A';
            }

            $export[] = $row;
        }

        return $export;
    }
}
