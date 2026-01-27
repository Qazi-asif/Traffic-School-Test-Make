<?php

namespace Database\Seeders\States;

use App\Models\Texas\Course;
use App\Models\Texas\Chapter;
use App\Models\Texas\ChapterQuiz;
use App\Models\Texas\QuizQuestion;
use Illuminate\Database\Seeder;

class TexasCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Texas Defensive Driving Course
        $defensiveCourse = Course::create([
            'title' => 'Texas Defensive Driving Course',
            'description' => 'TDLR-approved 6-hour defensive driving course for Texas traffic violations.',
            'course_details' => 'This course covers Texas traffic laws, defensive driving techniques, and safety practices.',
            'state_code' => 'TX',
            'min_pass_score' => 70,
            'total_duration' => 360, // 6 hours in minutes
            'price' => 34.95,
            'tdlr_course_id' => 'TX-DD-001',
            'certificate_template' => 'texas_defensive_template',
            'is_active' => true,
            'course_type' => 'defensive_driving',
            'delivery_type' => 'Internet',
            'requires_proctoring' => false,
            'defensive_driving_hours' => 6,
        ]);

        // Create Texas Driving Safety Course (with proctoring)
        $safetyCourse = Course::create([
            'title' => 'Texas Driving Safety Course (Proctored)',
            'description' => 'TDLR-approved proctored driving safety course for serious violations.',
            'course_details' => 'Advanced course with proctored final exam for serious traffic violations.',
            'state_code' => 'TX',
            'min_pass_score' => 80,
            'total_duration' => 480, // 8 hours in minutes
            'price' => 59.95,
            'tdlr_course_id' => 'TX-DSC-001',
            'certificate_template' => 'texas_safety_template',
            'is_active' => true,
            'course_type' => 'driving_safety',
            'delivery_type' => 'Internet',
            'requires_proctoring' => true,
            'defensive_driving_hours' => 8,
        ]);

        // Create chapters for defensive driving course
        $this->createDefensiveDrivingChapters($defensiveCourse);
        
        // Create chapters for safety course
        $this->createSafetyChapters($safetyCourse);
    }

    private function createDefensiveDrivingChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Texas Traffic Laws and Regulations',
                'content' => '<h2>Texas Traffic Laws</h2><p>Understanding Texas-specific traffic laws...</p>',
                'order_index' => 1,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'requires_video_completion' => false,
                'video_url' => null,
                'video_duration_minutes' => 0,
            ],
            [
                'title' => 'Defensive Driving Fundamentals',
                'content' => '<h2>Defensive Driving</h2><p>Core principles of defensive driving...</p>',
                'order_index' => 2,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'requires_video_completion' => true,
                'video_url' => 'https://example.com/texas-defensive-video.mp4',
                'video_duration_minutes' => 15,
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapter = Chapter::create(array_merge($chapterData, ['course_id' => $course->id]));
            $this->createChapterQuiz($chapter);
        }
    }
    private function createSafetyChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Advanced Safety Concepts',
                'content' => '<h2>Advanced Safety</h2><p>Advanced safety concepts for serious violations...</p>',
                'order_index' => 1,
                'duration_minutes' => 120,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'requires_video_completion' => true,
                'video_url' => 'https://example.com/texas-safety-video.mp4',
                'video_duration_minutes' => 20,
            ],
            [
                'title' => 'Risk Management and Prevention',
                'content' => '<h2>Risk Management</h2><p>Managing and preventing driving risks...</p>',
                'order_index' => 2,
                'duration_minutes' => 120,
                'is_active' => true,
                'enforce_minimum_time' => true,
                'requires_video_completion' => false,
                'video_url' => null,
                'video_duration_minutes' => 0,
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapter = Chapter::create(array_merge($chapterData, ['course_id' => $course->id]));
            $this->createChapterQuiz($chapter, true); // Proctored quizzes
        }
    }

    private function createChapterQuiz(Chapter $chapter, bool $requiresProctoring = false): void
    {
        $quiz = ChapterQuiz::create([
            'chapter_id' => $chapter->id,
            'title' => $chapter->title . ' Quiz',
            'description' => 'Test your knowledge of ' . $chapter->title,
            'passing_score' => 70,
            'time_limit_minutes' => 25,
            'max_attempts' => 3,
            'randomize_questions' => true,
            'show_correct_answers' => true,
            'is_active' => true,
            'requires_proctoring' => $requiresProctoring,
            'video_proctoring_enabled' => $requiresProctoring,
        ]);

        $this->createQuizQuestions($quiz, $chapter);
    }

    private function createQuizQuestions(ChapterQuiz $quiz, Chapter $chapter): void
    {
        $questions = [
            [
                'question_text' => 'What is the maximum speed limit on Texas rural interstates?',
                'question_type' => 'multiple_choice',
                'option_a' => '70 mph',
                'option_b' => '75 mph',
                'option_c' => '80 mph',
                'option_d' => '85 mph',
                'correct_answer' => 'C',
                'explanation' => 'The maximum speed limit on Texas rural interstates is 80 mph.',
                'points' => 10,
                'tdlr_approved' => true,
                'image_url' => null,
            ],
            [
                'question_text' => 'Texas law requires seat belt use for all passengers.',
                'question_type' => 'true_false',
                'option_a' => 'True',
                'option_b' => 'False',
                'correct_answer' => 'A',
                'explanation' => 'Texas law requires all passengers to wear seat belts.',
                'points' => 10,
                'tdlr_approved' => true,
                'image_url' => null,
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