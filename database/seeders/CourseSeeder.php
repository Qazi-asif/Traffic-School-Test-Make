<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        // Create Florida Traffic School Course in florida_courses table
        $courseId = DB::table('florida_courses')->insertGetId([
            'title' => 'Florida Basic Driver Improvement (BDI)',
            'course_type' => 'BDI',
            'description' => 'Complete 4-hour Basic Driver Improvement course for Florida drivers.',
            'state_code' => 'FL',
            'total_duration' => 240,
            'price' => 29.99,
            'min_pass_score' => 80,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create chapters
        $chapters = [
            [
                'title' => 'Introduction to Traffic Safety',
                'content' => '<h3>Welcome to Traffic Safety</h3><p>This chapter covers the basics of traffic safety and defensive driving techniques.</p>',
                'duration' => 60,
                'order_index' => 1,
            ],
            [
                'title' => 'Florida Traffic Laws',
                'content' => '<h3>Understanding Florida Traffic Laws</h3><p>Learn about specific traffic laws in the state of Florida.</p>',
                'duration' => 90,
                'order_index' => 2,
            ],
            [
                'title' => 'Defensive Driving Techniques',
                'content' => '<h3>Defensive Driving</h3><p>Master defensive driving techniques to avoid accidents.</p>',
                'duration' => 90,
                'order_index' => 3,
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => $chapterData['title'],
                'content' => $chapterData['content'],
                'duration' => $chapterData['duration'],
                'order_index' => $chapterData['order_index'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add sample questions for each chapter
            $questions = [
                [
                    'question_text' => 'What is the speed limit in residential areas in Florida?',
                    'question_type' => 'multiple_choice',
                    'options' => ['25 mph', '30 mph', '35 mph', '40 mph'],
                    'correct_answer' => '30 mph',
                    'explanation' => 'The speed limit in residential areas in Florida is 30 mph unless otherwise posted.',
                    'points' => 1,
                    'order_index' => 1,
                ],
                [
                    'question_text' => 'You must always yield to pedestrians in crosswalks.',
                    'question_type' => 'true_false',
                    'options' => ['true', 'false'],
                    'correct_answer' => 'true',
                    'explanation' => 'Pedestrians always have the right of way in marked crosswalks.',
                    'points' => 1,
                    'order_index' => 2,
                ],
            ];

            foreach ($questions as $questionData) {
                DB::table('questions')->insert([
                    'chapter_id' => $chapterId,
                    'course_id' => $courseId,
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => $questionData['explanation'] ?? null,
                    'points' => $questionData['points'],
                    'order_index' => $questionData['order_index'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Add final exam questions
        $finalExamQuestions = [
            [
                'question_text' => 'What should you do when approaching a yellow traffic light?',
                'question_type' => 'multiple_choice',
                'options' => ['Speed up', 'Stop if safe to do so', 'Continue at same speed', 'Honk your horn'],
                'correct_answer' => 'Stop if safe to do so',
                'explanation' => 'Yellow lights indicate you should prepare to stop if it is safe to do so.',
                'points' => 2,
                'order_index' => 1,
            ],
            [
                'question_text' => 'Defensive driving means driving to prevent accidents despite the actions of others.',
                'question_type' => 'true_false',
                'options' => ['true', 'false'],
                'correct_answer' => 'true',
                'explanation' => 'Defensive driving is about being proactive to prevent accidents.',
                'points' => 2,
                'order_index' => 2,
            ],
        ];

        foreach ($finalExamQuestions as $questionData) {
            DB::table('questions')->insert([
                'course_id' => $courseId,
                'chapter_id' => null,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'] ?? null,
                'points' => $questionData['points'],
                'order_index' => $questionData['order_index'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
