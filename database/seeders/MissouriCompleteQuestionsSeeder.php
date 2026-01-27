<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissouriCompleteQuestionsSeeder extends Seeder
{
    public function run()
    {
        $missouriCourse = DB::table('florida_courses')->where('state', 'Missouri')->first();
        if (! $missouriCourse) {
            $this->command->error('Missouri course not found!');

            return;
        }

        // Delete existing questions
        DB::table('questions')->where('course_id', $missouriCourse->id)->delete();

        $chapters = DB::table('chapters')->where('course_id', $missouriCourse->id)->orderBy('order_index')->get();

        $allQuestions = $this->getAllQuestions();

        foreach ($allQuestions as $chapterIndex => $questions) {
            $chapter = $chapters[$chapterIndex] ?? null;
            if (! $chapter) {
                continue;
            }

            foreach ($questions as $index => $q) {
                $options = [];
                $correctAnswer = '';

                foreach (['a', 'b', 'c', 'd', 'e'] as $key) {
                    if (! empty($q[$key])) {
                        $options[] = $q[$key];
                        if ($key === strtolower($q['correct'])) {
                            $correctAnswer = $q[$key];
                        }
                    }
                }

                DB::table('questions')->insert([
                    'chapter_id' => $chapter->id,
                    'course_id' => $missouriCourse->id,
                    'question_text' => $q['q'],
                    'question_type' => 'multiple_choice',
                    'options' => json_encode($options),
                    'correct_answer' => $correctAnswer,
                    'order_index' => $index + 1,
                    'points' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info('Chapter '.($chapterIndex + 1).': '.count($questions).' questions seeded');
        }

        $this->command->info('All Missouri questions seeded successfully!');
    }

    private function getAllQuestions()
    {
        // All questions extracted from Missouri quiz docx
        return include __DIR__.'/missouri_questions_data.php';
    }
}
