<?php

namespace Database\Seeders\States;

use App\Models\Florida\Course;
use App\Models\Florida\Chapter;
use App\Models\Florida\ChapterQuiz;
use App\Models\Florida\QuizQuestion;
use Illuminate\Database\Seeder;

class FloridaCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Florida BDI Course
        $bdiCourse = Course::create([
            'title' => 'Florida Basic Driver Improvement (BDI)',
            'description' => 'State-approved 4-hour Basic Driver Improvement course for Florida traffic violations.',
            'course_details' => 'This course covers Florida traffic laws, safe driving practices, and defensive driving techniques.',
            'state_code' => 'FL',
            'min_pass_score' => 80,
            'total_duration' => 240, // 4 hours in minutes
            'price' => 29.95,
            'dicds_course_id' => 'FL-BDI-001',
            'certificate_template' => 'florida_bdi_template',
            'copyright_protected' => true,
            'is_active' => true,
            'course_type' => 'BDI',
            'delivery_type' => 'Internet',
        ]);

        // Create Florida ADI Course
        $adiCourse = Course::create([
            'title' => 'Florida Advanced Driver Improvement (ADI)',
            'description' => 'State-approved 8-hour Advanced Driver Improvement course for serious traffic violations.',
            'course_details' => 'Advanced course covering defensive driving, hazard perception, and Florida traffic safety.',
            'state_code' => 'FL',
            'min_pass_score' => 85,
            'total_duration' => 480, // 8 hours in minutes
            'price' => 49.95,
            'dicds_course_id' => 'FL-ADI-001',
            'certificate_template' => 'florida_adi_template',
            'copyright_protected' => true,
            'is_active' => true,
            'course_type' => 'ADI',
            'delivery_type' => 'Internet',
        ]);

        // Create Florida TLSAE Course
        $tlsaeCourse = Course::create([
            'title' => 'Florida Traffic Law and Substance Abuse Education (TLSAE)',
            'description' => 'Required 4-hour course for first-time Florida driver license applicants.',
            'course_details' => 'Covers Florida traffic laws, substance abuse awareness, and safe driving practices.',
            'state_code' => 'FL',
            'min_pass_score' => 80,
            'total_duration' => 240, // 4 hours in minutes
            'price' => 24.95,
            'dicds_course_id' => 'FL-TLSAE-001',
            'certificate_template' => 'florida_tlsae_template',
            'copyright_protected' => true,
            'is_active' => true,
            'course_type' => 'TLSAE',
            'delivery_type' => 'Internet',
        ]);

        // Create chapters for BDI course
        $this->createBdiChapters($bdiCourse);
        
        // Create chapters for ADI course
        $this->createAdiChapters($adiCourse);
        
        // Create chapters for TLSAE course
        $this->createTlsaeChapters($tlsaeCourse);
    }

    private function createBdiChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Introduction to Defensive Driving',
                'content' => '<h2>Welcome to Florida BDI</h2><p>This course will help you become a safer, more responsible driver...</p>',
                'order_index' => 1,
                'duration_minutes' => 45,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Florida Traffic Laws',
                'content' => '<h2>Florida Traffic Laws</h2><p>Understanding Florida traffic laws is essential for safe driving...</p>',
                'order_index' => 2,
                'duration_minutes' => 60,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Hazard Recognition',
                'content' => '<h2>Identifying Road Hazards</h2><p>Learn to identify and respond to potential hazards on the road...</p>',
                'order_index' => 3,
                'duration_minutes' => 75,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Safe Driving Practices',
                'content' => '<h2>Safe Driving Techniques</h2><p>Master the techniques that will keep you and others safe...</p>',
                'order_index' => 4,
                'duration_minutes' => 60,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapter = Chapter::create(array_merge($chapterData, ['course_id' => $course->id]));
            $this->createChapterQuiz($chapter);
        }
    }

    private function createAdiChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Advanced Defensive Driving Concepts',
                'content' => '<h2>Advanced Defensive Driving</h2><p>Building on basic defensive driving principles...</p>',
                'order_index' => 1,
                'duration_minutes' => 90,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Risk Assessment and Management',
                'content' => '<h2>Risk Assessment</h2><p>Learn to assess and manage driving risks effectively...</p>',
                'order_index' => 2,
                'duration_minutes' => 120,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Collision Avoidance Techniques',
                'content' => '<h2>Collision Avoidance</h2><p>Advanced techniques for avoiding collisions...</p>',
                'order_index' => 3,
                'duration_minutes' => 135,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Attitude and Behavior Modification',
                'content' => '<h2>Changing Driving Behavior</h2><p>Understanding and modifying aggressive driving behaviors...</p>',
                'order_index' => 4,
                'duration_minutes' => 135,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
        ];

        foreach ($chapters as $chapterData) {
            $chapter = Chapter::create(array_merge($chapterData, ['course_id' => $course->id]));
            $this->createChapterQuiz($chapter);
        }
    }

    private function createTlsaeChapters(Course $course): void
    {
        $chapters = [
            [
                'title' => 'Florida Traffic Laws and Regulations',
                'content' => '<h2>Florida Traffic Laws</h2><p>Comprehensive overview of Florida traffic laws...</p>',
                'order_index' => 1,
                'duration_minutes' => 60,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Substance Abuse Awareness',
                'content' => '<h2>Substance Abuse and Driving</h2><p>Understanding the dangers of impaired driving...</p>',
                'order_index' => 2,
                'duration_minutes' => 60,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Safe Driving Fundamentals',
                'content' => '<h2>Safe Driving Basics</h2><p>Essential safe driving practices for new drivers...</p>',
                'order_index' => 3,
                'duration_minutes' => 60,
                'is_active' => true,
                'enforce_minimum_time' => true,
            ],
            [
                'title' => 'Responsibility and Consequences',
                'content' => '<h2>Driver Responsibility</h2><p>Understanding your responsibilities as a Florida driver...</p>',
                'order_index' => 4,
                'duration_minutes' => 60,
                'is_active' => true,
                'enforce_minimum_time' => true,
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
            'time_limit_minutes' => 15,
            'max_attempts' => 3,
            'randomize_questions' => true,
            'show_correct_answers' => true,
            'is_active' => true,
        ]);

        // Create sample quiz questions
        $this->createQuizQuestions($quiz, $chapter);
    }

    private function createQuizQuestions(ChapterQuiz $quiz, Chapter $chapter): void
    {
        $questions = [
            [
                'question_text' => 'What is the primary goal of defensive driving?',
                'question_type' => 'multiple_choice',
                'option_a' => 'To drive as fast as possible',
                'option_b' => 'To prevent accidents and reduce risks',
                'option_c' => 'To save fuel',
                'option_d' => 'To arrive on time',
                'correct_answer' => 'B',
                'explanation' => 'Defensive driving focuses on preventing accidents and reducing risks on the road.',
                'points' => 10,
                'difficulty_level' => 'easy',
            ],
            [
                'question_text' => 'In Florida, what is the speed limit in residential areas unless otherwise posted?',
                'question_type' => 'multiple_choice',
                'option_a' => '25 mph',
                'option_b' => '30 mph',
                'option_c' => '35 mph',
                'option_d' => '40 mph',
                'correct_answer' => 'B',
                'explanation' => 'The default speed limit in Florida residential areas is 30 mph unless otherwise posted.',
                'points' => 10,
                'difficulty_level' => 'medium',
            ],
            [
                'question_text' => 'When should you use your turn signals?',
                'question_type' => 'multiple_choice',
                'option_a' => 'Only when other cars are present',
                'option_b' => 'Only during the day',
                'option_c' => 'At least 100 feet before turning',
                'option_d' => 'Only on highways',
                'correct_answer' => 'C',
                'explanation' => 'Florida law requires turn signals to be activated at least 100 feet before turning.',
                'points' => 10,
                'difficulty_level' => 'medium',
            ],
            [
                'question_text' => 'Driving under the influence is a serious offense in Florida.',
                'question_type' => 'true_false',
                'option_a' => 'True',
                'option_b' => 'False',
                'correct_answer' => 'A',
                'explanation' => 'DUI is indeed a serious offense in Florida with severe penalties.',
                'points' => 10,
                'difficulty_level' => 'easy',
            ],
            [
                'question_text' => 'What should you do when approaching a yellow traffic light?',
                'question_type' => 'multiple_choice',
                'option_a' => 'Speed up to get through',
                'option_b' => 'Prepare to stop if safe to do so',
                'option_c' => 'Always stop immediately',
                'option_d' => 'Ignore it',
                'correct_answer' => 'B',
                'explanation' => 'Yellow lights warn that the light is about to turn red. Prepare to stop if you can do so safely.',
                'points' => 10,
                'difficulty_level' => 'medium',
            ],
        ];

        foreach ($questions as $index => $questionData) {
            QuizQuestion::create(array_merge($questionData, [
                'course_id' => $chapter->course_id,
                'chapter_id' => $chapter->id,
                'quiz_id' => $quiz->id,
                'order_index' => $index + 1,
                'is_active' => true,
            ]));
        }
    }
}