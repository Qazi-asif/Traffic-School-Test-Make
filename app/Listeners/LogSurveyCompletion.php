<?php

namespace App\Listeners;

use App\Events\SurveyCompleted;
use Illuminate\Support\Facades\Log;

class LogSurveyCompletion
{
    public function handle(SurveyCompleted $event): void
    {
        $response = $event->surveyResponse;

        Log::info('Survey completed', [
            'survey_id' => $response->survey_id,
            'user_id' => $response->user_id,
            'enrollment_id' => $response->enrollment_id,
            'completed_at' => $response->completed_at,
        ]);
    }
}
