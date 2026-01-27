<?php

namespace App\Events;

use App\Models\SurveyResponse;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveyCompleted
{
    use Dispatchable, SerializesModels;

    public $surveyResponse;

    public function __construct(SurveyResponse $surveyResponse)
    {
        $this->surveyResponse = $surveyResponse;
    }
}
