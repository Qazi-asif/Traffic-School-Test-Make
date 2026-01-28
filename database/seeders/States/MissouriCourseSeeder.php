<?php

namespace Database\Seeders\States;

use App\Models\Missouri\Course;
use App\Models\Missouri\Chapter;
use App\Models\Missouri\ChapterQuiz;
use App\Models\Missouri\QuizQuestion;
use Illuminate\Database\Seeder;

class MissouriCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Missouri Defensive Driving Course
        $defensiveCourse = Course::create([
            'title' => 'Missouri Defensive Driving Course',
            'description' => 'State-approved defensive driving course for Missouri traffic violations.',
            'course_details' => 'This course covers Missouri traffic laws, defensive driving, and Form 4444 completion.',
            'state_code' => 'MO',
            'min_pass_score' => 80,
            'total_duration' => 480, // 8 hours in minutes
            'price' => 39.95,
            'form_4444_template' => 'missouri_form_4444_template',
            'is_active' => true,
            'course_type' => 'Defensive Driving',
            'delivery_type' => 'Internet',
            'requires_form_4444' => true,
        ]);

        // Create Missouri Point Reduction Course
        $pointReductionCourse = Course::create([
            'title' => 'Missouri Point Reduction Course',
            'description' => 'Reduce points on your Missouri driving record.',
            'course_details' => 'Complete this course to reduce points and improve your driving record.',
            'state_code' => 'MO',
            'min_pass_score' => 75,
            'total_duration' => 360, // 6 hours in minutes
            'price' => 34.95,
            'form_4444_template' => 'missouri_form_4444_template',
            'is_active' => true,
            'course_type' => 'Point Reduction',
            'delivery_type' => 'Internet',
            'requires_form_4444' => true,
        ]);

        // Create chapters for defensive driving course
        $this->createDefensiveDrivingChapters($defensiveCourse);
        
        // Create chapters for point reduction course
        $this->createPointReductionChapters($pointReductionCourse);
    }

    private function createDefensiveDrivingChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Missouri Traffic Laws and Regulations',
                'content' => '<h2>Missouri Traffic Laws</h2><p>Understanding Missouri-specific traffic laws and regulations...</p>',
                'order_index' => 1,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_bank_id' => null,
            ],
            [
                'title' => 'Defensive Driving Techniques',
                'content' => '<h2>Defensive Driving</h2><p>Learn proven defensive driving techniques...</p>',
                'order_index' => 2,
                'duration_minutes' => 120,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_bank_id' => null,
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapter = Chapter::create(array_merge($chapterData, ['course_id' => $course->id]));
            $this->createChapterQuiz($chapter);
        }
    }
    private function createPointReductionChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Understanding Point Systems',
                'content' => '<h2>Missouri Point System</h2><p>Learn how the Missouri point system works...</p>',
                'order_index' => 1,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_bank_id' => null,
            ],
            [
                'title' => 'Safe Driving Practices',
                'content' => '<h2>Safe Driving</h2><p>Practices to maintain a clean driving record...</p>',
                'order_index' => 2,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_bank_id' => null,
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapter = Chapter::create(array_merge($chapterData, ['course_id' => $course->id]));
            $this->createChapterQuiz($chapter);
        }
    }

    private function createChapterQuiz(Chapter $chapter): void
    {
        $quiz = ChapterQuiz::create([
            'chapter_id' => $chapter->id,
            'title' => $chapter->title . ' Quiz',
            'description' => 'Test your knowledge of ' . $chapter->title,
            'passing_score' => 80,
            'time_limit_minutes' => 20,
            'max_attempts' => 3,
            'randomize_questions' => true,
            'show_correct_answers' => true,
            'is_active' => true,
            'quiz_bank_rotation' => true,
        ]);

        $this->createQuizQuestions($quiz, $chapter);
    }

    private function createQuizQuestions(ChapterQuiz $quiz, Chapter $chapter): void
    {
        $questions = [
            [
                'question_text' => 'What is the speed limit on Missouri interstate highways?',
                'question_type' => 'multiple_choice',
                'option_a' => '65 mph',
                'option_b' => '70 mph',
                'option_c' => '75 mph',
                'option_d' => '80 mph',
                'correct_answer' => 'B',
                'explanation' => 'The speed limit on Missouri interstate highways is typically 70 mph.',
                'points' => 10,
                'quiz_set' => 'A',
                'rotation_group' => 1,
            ],
            [
                'question_text' => 'Missouri requires drivers to have liability insurance.',
                'question_type' => 'true_false',
                'option_a' => 'True',
                'option_b' => 'False',
                'correct_answer' => 'A',
                'explanation' => 'Missouri law requires all drivers to carry liability insurance.',
                'points' => 10,
                'quiz_set' => 'A',
                'rotation_group' => 1,
            ],
        ];

        foreach ($questions as $index => $questionData) {
            QuizQuestion::create(array_merge($questionData, [
                'course_id' => $chapter->course_id,
                'chapter_id' => $chapter->id,
                'quiz_id' => $quiz->id,
                'order_index' => $index + 1,
                'is_active' => true,
                'difficulty_level' => 'medium',
            ]));
        }
    }
}