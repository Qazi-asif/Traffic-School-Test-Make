<?php

namespace Database\Seeders\States;

use App\Models\Delaware\Course;
use App\Models\Delaware\Chapter;
use App\Models\Delaware\ChapterQuiz;
use App\Models\Delaware\QuizQuestion;
use Illuminate\Database\Seeder;

class DelawareCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Delaware Defensive Driving Course
        $defensiveCourse = Course::create([
            'title' => 'Delaware Defensive Driving Course',
            'description' => 'State-approved defensive driving course for Delaware traffic violations.',
            'course_details' => 'This course covers Delaware traffic laws and defensive driving techniques.',
            'state_code' => 'DE',
            'min_pass_score' => 80,
            'total_duration' => 360, // 6 hours in minutes
            'price' => 32.95,
            'dmv_course_id' => 'DE-DD-001',
            'certificate_template' => 'delaware_defensive_template',
            'is_active' => true,
            'course_type' => 'defensive_driving',
            'delivery_type' => 'Internet',
            'quiz_rotation_enabled' => true,
            'aggressive_driving_course' => false,
            'insurance_discount_eligible' => true,
        ]);

        // Create Delaware Aggressive Driving Course
        $aggressiveCourse = Course::create([
            'title' => 'Delaware Aggressive Driving Course',
            'description' => 'Specialized course for aggressive driving violations in Delaware.',
            'course_details' => 'Focused on aggressive driving behaviors and prevention techniques.',
            'state_code' => 'DE',
            'min_pass_score' => 85,
            'total_duration' => 480, // 8 hours in minutes
            'price' => 44.95,
            'dmv_course_id' => 'DE-AD-001',
            'certificate_template' => 'delaware_aggressive_template',
            'is_active' => true,
            'course_type' => 'aggressive_driving',
            'delivery_type' => 'Internet',
            'quiz_rotation_enabled' => true,
            'aggressive_driving_course' => true,
            'insurance_discount_eligible' => false,
        ]);

        // Create Delaware Insurance Discount Course
        $insuranceCourse = Course::create([
            'title' => 'Delaware Insurance Discount Course',
            'description' => '3-hour refresher course for insurance discounts.',
            'course_details' => 'Refresher course to qualify for auto insurance discounts.',
            'state_code' => 'DE',
            'min_pass_score' => 75,
            'total_duration' => 180, // 3 hours in minutes
            'price' => 24.95,
            'dmv_course_id' => 'DE-ID-001',
            'certificate_template' => 'delaware_insurance_template',
            'is_active' => true,
            'course_type' => 'insurance_discount',
            'delivery_type' => 'Internet',
            'quiz_rotation_enabled' => true,
            'aggressive_driving_course' => false,
            'insurance_discount_eligible' => true,
        ]);

        // Create chapters for each course
        $this->createDefensiveDrivingChapters($defensiveCourse);
        $this->createAggressiveDrivingChapters($aggressiveCourse);
        $this->createInsuranceDiscountChapters($insuranceCourse);
    }
    private function createDefensiveDrivingChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Delaware Traffic Laws',
                'content' => '<h2>Delaware Traffic Laws</h2><p>Understanding Delaware-specific traffic laws...</p>',
                'order_index' => 1,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_rotation_set' => 'A',
                'has_interactive_content' => true,
                'interactive_content_url' => 'https://example.com/delaware-interactive-1',
            ],
            [
                'title' => 'Defensive Driving Techniques',
                'content' => '<h2>Defensive Driving</h2><p>Proven defensive driving techniques...</p>',
                'order_index' => 2,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_rotation_set' => 'B',
                'has_interactive_content' => false,
                'interactive_content_url' => null,
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapter = Chapter::create(array_merge($chapterData, ['course_id' => $course->id]));
            $this->createChapterQuiz($chapter);
        }
    }

    private function createAggressiveDrivingChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Understanding Aggressive Driving',
                'content' => '<h2>Aggressive Driving</h2><p>Identifying and preventing aggressive driving behaviors...</p>',
                'order_index' => 1,
                'duration_minutes' => 120,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_rotation_set' => 'A',
                'has_interactive_content' => true,
                'interactive_content_url' => 'https://example.com/delaware-aggressive-1',
            ],
            [
                'title' => 'Anger Management and Road Rage',
                'content' => '<h2>Anger Management</h2><p>Managing anger and preventing road rage incidents...</p>',
                'order_index' => 2,
                'duration_minutes' => 120,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_rotation_set' => 'B',
                'has_interactive_content' => true,
                'interactive_content_url' => 'https://example.com/delaware-aggressive-2',
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapter = Chapter::create(array_merge($chapterData, ['course_id' => $course->id]));
            $this->createChapterQuiz($chapter);
        }
    }

    private function createInsuranceDiscountChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Safe Driving Refresher',
                'content' => '<h2>Safe Driving</h2><p>Refresher on safe driving practices...</p>',
                'order_index' => 1,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'quiz_rotation_set' => 'A',
                'has_interactive_content' => false,
                'interactive_content_url' => null,
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
            'rotation_enabled' => true,
            'rotation_sets' => ['A', 'B', 'C'],
        ]);

        $this->createQuizQuestions($quiz, $chapter);
    }

    private function createQuizQuestions(ChapterQuiz $quiz, Chapter $chapter): void
    {
        $rotationSets = ['A', 'B', 'C'];
        
        foreach ($rotationSets as $set) {
            $questions = [
                [
                    'question_text' => "What is the speed limit in Delaware school zones? (Set $set)",
                    'question_type' => 'multiple_choice',
                    'option_a' => '15 mph',
                    'option_b' => '20 mph',
                    'option_c' => '25 mph',
                    'option_d' => '30 mph',
                    'correct_answer' => 'B',
                    'explanation' => 'Delaware school zones have a 20 mph speed limit.',
                    'points' => 10,
                    'quiz_rotation_set' => $set,
                    'aggressive_driving_related' => false,
                    'insurance_discount_topic' => true,
                ],
                [
                    'question_text' => "Delaware requires hands-free device use while driving. (Set $set)",
                    'question_type' => 'true_false',
                    'option_a' => 'True',
                    'option_b' => 'False',
                    'correct_answer' => 'A',
                    'explanation' => 'Delaware law requires hands-free device use while driving.',
                    'points' => 10,
                    'quiz_rotation_set' => $set,
                    'aggressive_driving_related' => true,
                    'insurance_discount_topic' => true,
                ],
            ];

            foreach ($questions as $index => $questionData) {
                QuizQuestion::create(array_merge($questionData, [
                    'course_id' => $chapter->course_id,
                    'chapter_id' => $chapter->id,
                    'quiz_id' => $quiz->id,
                    'order_index' => ($index + 1) + (array_search($set, $rotationSets) * 10),
                    'is_active' => true,
                    'difficulty_level' => 'medium',
                ]));
            }
        }
    }
}