<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        // General satisfaction survey (all states)
        $generalSurvey = Survey::create([
            'name' => 'General Course Satisfaction Survey',
            'description' => 'Help us improve our courses by sharing your feedback.',
            'state_code' => null,
            'course_id' => null,
            'is_active' => true,
            'is_required' => true,
            'display_order' => 0,
        ]);

        $this->createGeneralQuestions($generalSurvey);

        // Florida-specific survey
        $floridaSurvey = Survey::create([
            'name' => 'Florida Course Feedback',
            'description' => 'Florida-specific course evaluation.',
            'state_code' => 'FL',
            'course_id' => null,
            'is_active' => true,
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->createFloridaQuestions($floridaSurvey);

        // Delaware-specific survey
        $delawareSurvey = Survey::create([
            'name' => 'Delaware Course Evaluation',
            'description' => 'Delaware defensive driving course feedback.',
            'state_code' => 'DE',
            'course_id' => null,
            'is_active' => true,
            'is_required' => true,
            'display_order' => 2,
        ]);

        $this->createDelawareQuestions($delawareSurvey);

        // Missouri-specific survey
        $missouriSurvey = Survey::create([
            'name' => 'Missouri Course Feedback',
            'description' => 'Missouri driver improvement program evaluation.',
            'state_code' => 'MO',
            'course_id' => null,
            'is_active' => true,
            'is_required' => true,
            'display_order' => 3,
        ]);

        $this->createMissouriQuestions($missouriSurvey);
    }

    protected function createGeneralQuestions(Survey $survey): void
    {
        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'How would you rate the overall course experience?',
            'question_type' => 'scale_1_5',
            'options' => null,
            'is_required' => true,
            'display_order' => 0,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Was the course content easy to understand?',
            'question_type' => 'scale_1_5',
            'options' => null,
            'is_required' => true,
            'display_order' => 1,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'How would you rate the course materials?',
            'question_type' => 'scale_1_5',
            'options' => null,
            'is_required' => true,
            'display_order' => 2,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Would you recommend this course to others?',
            'question_type' => 'yes_no',
            'options' => null,
            'is_required' => true,
            'display_order' => 3,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'What could we improve?',
            'question_type' => 'text',
            'options' => null,
            'is_required' => false,
            'display_order' => 4,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'How did you hear about us?',
            'question_type' => 'multiple_choice',
            'options' => ['Google', 'Friend', 'Court', 'Advertisement', 'Other'],
            'is_required' => true,
            'display_order' => 5,
        ]);
    }

    protected function createFloridaQuestions(Survey $survey): void
    {
        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'How satisfied are you with the Florida-specific content?',
            'question_type' => 'scale_1_5',
            'options' => null,
            'is_required' => true,
            'display_order' => 0,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Did the course meet Florida DHSMV requirements?',
            'question_type' => 'yes_no',
            'options' => null,
            'is_required' => true,
            'display_order' => 1,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Rate the quality of Florida traffic law information',
            'question_type' => 'scale_1_10',
            'options' => null,
            'is_required' => true,
            'display_order' => 2,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Additional comments about the Florida course',
            'question_type' => 'text',
            'options' => null,
            'is_required' => false,
            'display_order' => 3,
        ]);
    }

    protected function createDelawareQuestions(Survey $survey): void
    {
        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Overall satisfaction with Delaware defensive driving course',
            'question_type' => 'scale_1_5',
            'options' => null,
            'is_required' => true,
            'display_order' => 0,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Was the 6-hour course length appropriate?',
            'question_type' => 'yes_no',
            'options' => null,
            'is_required' => true,
            'display_order' => 1,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Rate the Delaware-specific traffic scenarios',
            'question_type' => 'scale_1_5',
            'options' => null,
            'is_required' => true,
            'display_order' => 2,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'What did you find most valuable in this course?',
            'question_type' => 'text',
            'options' => null,
            'is_required' => false,
            'display_order' => 3,
        ]);
    }

    protected function createMissouriQuestions(Survey $survey): void
    {
        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'How would you rate the Missouri driver improvement program?',
            'question_type' => 'scale_1_5',
            'options' => null,
            'is_required' => true,
            'display_order' => 0,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Did you find the Missouri-specific content helpful?',
            'question_type' => 'yes_no',
            'options' => null,
            'is_required' => true,
            'display_order' => 1,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Rate the quality of instruction',
            'question_type' => 'scale_1_10',
            'options' => null,
            'is_required' => true,
            'display_order' => 2,
        ]);

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => 'Suggestions for improvement',
            'question_type' => 'text',
            'options' => null,
            'is_required' => false,
            'display_order' => 3,
        ]);
    }
}
