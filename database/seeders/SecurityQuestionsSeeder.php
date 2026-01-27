<?php

namespace Database\Seeders;

use App\Models\SecurityQuestion;
use Illuminate\Database\Seeder;

class SecurityQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            [
                'question_key' => 'q1',
                'question_text' => 'When does your driver\'s license expire?',
                'answer_type' => 'number',
                'help_text' => '(Year only, e.g., 2025)',
                'is_active' => true,
                'order_index' => 1,
            ],
            [
                'question_key' => 'q2',
                'question_text' => 'What is the weight listed on your driver\'s license?',
                'answer_type' => 'number',
                'help_text' => '(Numbers only, e.g., 162)',
                'is_active' => true,
                'order_index' => 2,
            ],
            [
                'question_key' => 'q3',
                'question_text' => 'How many cars do you own?',
                'answer_type' => 'number',
                'help_text' => '(Numbers only, e.g., 1)',
                'is_active' => true,
                'order_index' => 3,
            ],
            [
                'question_key' => 'q4',
                'question_text' => 'What are the last four digits of your Driver\'s License Number?',
                'answer_type' => 'text',
                'help_text' => '(e.g., 6374)',
                'is_active' => true,
                'order_index' => 4,
            ],
            [
                'question_key' => 'q5',
                'question_text' => 'What is your age?',
                'answer_type' => 'number',
                'help_text' => '(Numbers only, e.g., 31)',
                'is_active' => true,
                'order_index' => 5,
            ],
            [
                'question_key' => 'q6',
                'question_text' => 'How old were you when you got your Driver\'s License?',
                'answer_type' => 'number',
                'help_text' => '(Numbers only, e.g., 16)',
                'is_active' => true,
                'order_index' => 6,
            ],
            [
                'question_key' => 'q7',
                'question_text' => 'What zip code do you live in?',
                'answer_type' => 'text',
                'help_text' => '(e.g., 90210)',
                'is_active' => true,
                'order_index' => 7,
            ],
            [
                'question_key' => 'q8',
                'question_text' => 'In what year were you born?',
                'answer_type' => 'number',
                'help_text' => '(e.g., 1980)',
                'is_active' => true,
                'order_index' => 8,
            ],
            [
                'question_key' => 'q9',
                'question_text' => 'What color is your hair?',
                'answer_type' => 'text',
                'help_text' => null,
                'is_active' => true,
                'order_index' => 9,
            ],
            [
                'question_key' => 'q10',
                'question_text' => 'What city do you live in?',
                'answer_type' => 'text',
                'help_text' => null,
                'is_active' => true,
                'order_index' => 10,
            ],
        ];

        foreach ($questions as $question) {
            SecurityQuestion::updateOrCreate(
                ['question_key' => $question['question_key']],
                $question
            );
        }
    }
}
