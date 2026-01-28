<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Chapter;
use App\Models\ChapterQuestion;
use App\Models\FinalExamQuestion;

class MultiStateQuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding multi-state quiz questions...');

        // Seed chapter questions for each state
        $this->seedFloridaQuestions();
        $this->seedMissouriQuestions();
        $this->seedTexasQuestions();
        $this->seedDelawareQuestions();
        
        // Seed final exam questions
        $this->seedFinalExamQuestions();

        $this->command->info('Multi-state quiz questions seeded successfully!');
    }

    private function seedFloridaQuestions()
    {
        $this->command->info('Seeding Florida quiz questions...');

        // Get Florida chapters
        $chapters = Chapter::where('state_code', 'FL')->get();

        foreach ($chapters as $chapter) {
            $questions = $this->getFloridaChapterQuestions($chapter->title);
            
            foreach ($questions as $questionData) {
                ChapterQuestion::create([
                    'chapter_id' => $chapter->id,
                    'question_text' => $questionData['question'],
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => $questionData['explanation'] ?? '',
                    'state_specific' => 'FL',
                    'difficulty_level' => $questionData['difficulty'] ?? 1,
                    'order_index' => $questionData['order'] ?? 1,
                    'is_active' => true
                ]);
            }
        }
    }

    private function seedMissouriQuestions()
    {
        $this->command->info('Seeding Missouri quiz questions...');

        $chapters = Chapter::where('state_code', 'MO')->get();

        foreach ($chapters as $chapter) {
            $questions = $this->getMissouriChapterQuestions($chapter->title);
            
            foreach ($questions as $questionData) {
                ChapterQuestion::create([
                    'chapter_id' => $chapter->id,
                    'question_text' => $questionData['question'],
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => $questionData['explanation'] ?? '',
                    'state_specific' => 'MO',
                    'difficulty_level' => $questionData['difficulty'] ?? 1,
                    'order_index' => $questionData['order'] ?? 1,
                    'is_active' => true
                ]);
            }
        }
    }

    private function seedTexasQuestions()
    {
        $this->command->info('Seeding Texas quiz questions...');

        $chapters = Chapter::where('state_code', 'TX')->get();

        foreach ($chapters as $chapter) {
            $questions = $this->getTexasChapterQuestions($chapter->title);
            
            foreach ($questions as $questionData) {
                ChapterQuestion::create([
                    'chapter_id' => $chapter->id,
                    'question_text' => $questionData['question'],
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => $questionData['explanation'] ?? '',
                    'state_specific' => 'TX',
                    'difficulty_level' => $questionData['difficulty'] ?? 1,
                    'order_index' => $questionData['order'] ?? 1,
                    'is_active' => true
                ]);
            }
        }
    }

    private function seedDelawareQuestions()
    {
        $this->command->info('Seeding Delaware quiz questions...');

        $chapters = Chapter::where('state_code', 'DE')->get();

        foreach ($chapters as $chapter) {
            $questions = $this->getDelawareChapterQuestions($chapter->title);
            
            foreach ($questions as $questionData) {
                ChapterQuestion::create([
                    'chapter_id' => $chapter->id,
                    'question_text' => $questionData['question'],
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => $questionData['explanation'] ?? '',
                    'state_specific' => 'DE',
                    'difficulty_level' => $questionData['difficulty'] ?? 1,
                    'order_index' => $questionData['order'] ?? 1,
                    'is_active' => true
                ]);
            }
        }
    }

    private function seedFinalExamQuestions()
    {
        $this->command->info('Seeding final exam questions...');

        // Get all courses by state
        $floridaCourses = DB::table('florida_courses')->where('is_active', true)->get();
        $missouriCourses = DB::table('missouri_courses')->where('is_active', true)->get();
        $texasCourses = DB::table('texas_courses')->where('is_active', true)->get();
        $delawareCourses = DB::table('delaware_courses')->where('is_active', true)->get();

        // Seed Florida final exam questions
        foreach ($floridaCourses as $course) {
            $questions = $this->getFloridaFinalExamQuestions();
            foreach ($questions as $questionData) {
                FinalExamQuestion::create([
                    'course_id' => $course->id,
                    'course_table' => 'florida_courses',
                    'question_text' => $questionData['question'],
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => $questionData['explanation'] ?? '',
                    'state_specific' => 'FL',
                    'difficulty_level' => $questionData['difficulty'] ?? 1,
                    'is_active' => true
                ]);
            }
        }

        // Seed Missouri final exam questions
        foreach ($missouriCourses as $course) {
            $questions = $this->getMissouriFinalExamQuestions();
            foreach ($questions as $questionData) {
                FinalExamQuestion::create([
                    'course_id' => $course->id,
                    'course_table' => 'missouri_courses',
                    'question_text' => $questionData['question'],
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => $questionData['explanation'] ?? '',
                    'state_specific' => 'MO',
                    'difficulty_level' => $questionData['difficulty'] ?? 1,
                    'is_active' => true
                ]);
            }
        }

        // Similar for Texas and Delaware...
    }

    private function getFloridaChapterQuestions($chapterTitle)
    {
        if (strpos($chapterTitle, 'Introduction') !== false) {
            return [
                [
                    'question' => 'What is the primary goal of Florida\'s Basic Driver Improvement course?',
                    'options' => [
                        'A' => 'To increase driving speed',
                        'B' => 'To improve driving safety and reduce violations',
                        'C' => 'To learn about car maintenance',
                        'D' => 'To get a commercial license'
                    ],
                    'correct_answer' => 'B',
                    'explanation' => 'The BDI course is designed to improve driving safety and reduce future traffic violations.',
                    'difficulty' => 1,
                    'order' => 1
                ],
                [
                    'question' => 'How many hours is the Florida BDI course?',
                    'options' => [
                        'A' => '2 hours',
                        'B' => '4 hours',
                        'C' => '6 hours',
                        'D' => '8 hours'
                    ],
                    'correct_answer' => 'B',
                    'explanation' => 'The Florida Basic Driver Improvement course is 4 hours long.',
                    'difficulty' => 1,
                    'order' => 2
                ]
            ];
        } elseif (strpos($chapterTitle, 'Traffic Laws') !== false) {
            return [
                [
                    'question' => 'What is the speed limit in a Florida school zone when children are present?',
                    'options' => [
                        'A' => '15 mph',
                        'B' => '20 mph',
                        'C' => '25 mph',
                        'D' => '30 mph'
                    ],
                    'correct_answer' => 'B',
                    'explanation' => 'Florida school zones have a 20 mph speed limit when children are present.',
                    'difficulty' => 2,
                    'order' => 1
                ]
            ];
        }

        return [];
    }

    private function getMissouriChapterQuestions($chapterTitle)
    {
        if (strpos($chapterTitle, 'Introduction') !== false) {
            return [
                [
                    'question' => 'How many hours is the Missouri Defensive Driving course?',
                    'options' => [
                        'A' => '4 hours',
                        'B' => '6 hours',
                        'C' => '8 hours',
                        'D' => '12 hours'
                    ],
                    'correct_answer' => 'C',
                    'explanation' => 'The Missouri Defensive Driving course is 8 hours long.',
                    'difficulty' => 1,
                    'order' => 1
                ]
            ];
        }

        return [];
    }

    private function getTexasChapterQuestions($chapterTitle)
    {
        if (strpos($chapterTitle, 'Introduction') !== false) {
            return [
                [
                    'question' => 'How many hours is the Texas Defensive Driving course?',
                    'options' => [
                        'A' => '4 hours',
                        'B' => '6 hours',
                        'C' => '8 hours',
                        'D' => '10 hours'
                    ],
                    'correct_answer' => 'B',
                    'explanation' => 'The Texas Defensive Driving course is 6 hours long.',
                    'difficulty' => 1,
                    'order' => 1
                ]
            ];
        }

        return [];
    }

    private function getDelawareChapterQuestions($chapterTitle)
    {
        if (strpos($chapterTitle, 'Introduction') !== false) {
            return [
                [
                    'question' => 'Delaware offers defensive driving courses in which durations?',
                    'options' => [
                        'A' => '3 hours only',
                        'B' => '6 hours only',
                        'C' => 'Both 3 hours and 6 hours',
                        'D' => '8 hours only'
                    ],
                    'correct_answer' => 'C',
                    'explanation' => 'Delaware offers both 3-hour and 6-hour defensive driving courses.',
                    'difficulty' => 1,
                    'order' => 1
                ]
            ];
        }

        return [];
    }

    private function getFloridaFinalExamQuestions()
    {
        return [
            [
                'question' => 'What percentage must you score to pass the Florida BDI final exam?',
                'options' => [
                    'A' => '70%',
                    'B' => '75%',
                    'C' => '80%',
                    'D' => '85%'
                ],
                'correct_answer' => 'C',
                'explanation' => 'You must score 80% or higher to pass the Florida BDI final exam.',
                'difficulty' => 1
            ],
            [
                'question' => 'What is the main purpose of defensive driving?',
                'options' => [
                    'A' => 'To drive faster',
                    'B' => 'To anticipate and avoid dangerous situations',
                    'C' => 'To use more fuel',
                    'D' => 'To ignore traffic laws'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Defensive driving focuses on anticipating and avoiding dangerous situations.',
                'difficulty' => 1
            ]
        ];
    }

    private function getMissouriFinalExamQuestions()
    {
        return [
            [
                'question' => 'What percentage must you score to pass the Missouri Defensive Driving final exam?',
                'options' => [
                    'A' => '70%',
                    'B' => '75%',
                    'C' => '80%',
                    'D' => '85%'
                ],
                'correct_answer' => 'A',
                'explanation' => 'You must score 70% or higher to pass the Missouri Defensive Driving final exam.',
                'difficulty' => 1
            ]
        ];
    }
}