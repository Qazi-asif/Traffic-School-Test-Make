<?php

namespace Database\Seeders;

use App\Models\MissouriCourseStructure;
use App\Models\MissouriQuizBank;
use Illuminate\Database\Seeder;

class CompleteQuizSeeder extends Seeder
{
    public function run()
    {
        $chapters = [
            1 => 'Missouri Traffic Laws',
            2 => 'Road Signs and Signals',
            3 => 'Intersections and Turns',
            4 => 'Licensing and Legal Requirements',
            5 => 'Highway Driving',
            6 => 'Lane Usage and Positioning',
            7 => 'Speed Laws and Backing',
            8 => 'Vehicle Equipment and Weather',
            9 => 'DUI and Substance Abuse',
            10 => 'Defensive Driving and Road Rage',
        ];

        foreach ($chapters as $num => $title) {
            $chapter = MissouriCourseStructure::create([
                'chapter_number' => $num,
                'chapter_title' => $title,
                'quiz_questions_count' => 10,
                'passing_score' => 80,
            ]);

            $this->seedChapterQuestions($chapter->id, $num);
        }
    }

    private function seedChapterQuestions($chapterId, $chapterNum)
    {
        $questions = $this->getQuestionsByChapter($chapterNum);

        foreach ($questions as $q) {
            MissouriQuizBank::create([
                'chapter_id' => $chapterId,
                'question_text' => $q['question'],
                'option_a' => $q['a'],
                'option_b' => $q['b'],
                'option_c' => $q['c'],
                'option_d' => $q['d'],
                'correct_answer' => $q['correct'],
                'category' => $q['category'],
                'difficulty_level' => 'medium',
            ]);
        }
    }

    private function getQuestionsByChapter($chapter)
    {
        $allQuestions = [
            1 => [ // Missouri Traffic Laws
                [
                    'question' => 'The two-way left turn lane_______.',
                    'a' => 'may not be used for passing',
                    'b' => 'Can never be used for U-turns',
                    'c' => 'Are set aside for the use of vehicles turning left or right',
                    'd' => 'Both C and B are correct',
                    'correct' => 'A',
                    'category' => 'traffic_laws',
                ],
                [
                    'question' => 'You should scan the road__________ ahead of your vehicle.',
                    'a' => '1 to 2 seconds',
                    'b' => '30 to 35 seconds',
                    'c' => '10-15 seconds',
                    'd' => '½ mile',
                    'correct' => 'C',
                    'category' => 'safe_driving',
                ],
            ],
            2 => [ // Road Signs
                [
                    'question' => 'The purpose of traffic signs are',
                    'a' => 'to serve as traffic control',
                    'b' => 'to communicate warnings',
                    'c' => 'to Express traffic regulations',
                    'd' => 'all of the above',
                    'correct' => 'D',
                    'category' => 'road_signs',
                ],
                [
                    'question' => 'A circular sign with letters R R alerts the driver of',
                    'a' => 'approaching railroad crossing',
                    'b' => 'rough road conditions',
                    'c' => 'road construction',
                    'd' => 'none of the above',
                    'correct' => 'A',
                    'category' => 'road_signs',
                ],
            ],
        ];

        return $allQuestions[$chapter] ?? [];
    }
}
