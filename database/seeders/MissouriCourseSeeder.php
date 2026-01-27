<?php

namespace Database\Seeders;

use App\Models\MissouriCourseStructure;
use App\Models\MissouriQuizBank;
use Illuminate\Database\Seeder;

class MissouriCourseSeeder extends Seeder
{
    public function run()
    {
        $chapters = [
            1 => 'Missouri Traffic Laws',
            2 => 'Road Signs and Signals',
            3 => 'Defensive Driving Techniques',
            4 => 'Highway and Interstate Driving',
            5 => 'Night Driving Safety',
            6 => 'Vehicle Maintenance and Safety',
            7 => 'DUI and Substance Abuse Laws',
            8 => 'Weather and Road Conditions',
            9 => 'Emergency Procedures',
            10 => 'Sharing the Road',
            11 => 'Missouri Point System and Penalties',
        ];

        foreach ($chapters as $number => $title) {
            $chapter = MissouriCourseStructure::create([
                'chapter_number' => $number,
                'chapter_title' => $title,
                'content' => "Content for {$title} chapter",
                'quiz_questions_count' => 10,
                'passing_score' => 80,
                'time_requirement_minutes' => 30,
            ]);

            // Add sample quiz questions for each chapter
            $this->createSampleQuestions($chapter->id, $number);
        }
    }

    private function createSampleQuestions($chapterId, $chapterNumber)
    {
        $sampleQuestions = [
            1 => [ // Missouri Traffic Laws
                [
                    'question_text' => 'What is the speed limit in Missouri school zones when children are present?',
                    'option_a' => '15 mph',
                    'option_b' => '20 mph',
                    'option_c' => '25 mph',
                    'option_d' => '30 mph',
                    'correct_answer' => 'B',
                    'category' => 'traffic_laws',
                ],
            ],
            2 => [ // Road Signs
                [
                    'question_text' => 'What does a yellow diamond-shaped sign indicate?',
                    'option_a' => 'Stop required',
                    'option_b' => 'Warning or caution',
                    'option_c' => 'No parking',
                    'option_d' => 'Speed limit',
                    'correct_answer' => 'B',
                    'category' => 'road_signs',
                ],
            ],
        ];

        if (isset($sampleQuestions[$chapterNumber])) {
            foreach ($sampleQuestions[$chapterNumber] as $question) {
                MissouriQuizBank::create(array_merge($question, [
                    'chapter_id' => $chapterId,
                    'difficulty_level' => 'medium',
                    'state_required' => true,
                ]));
            }
        }
    }
}
