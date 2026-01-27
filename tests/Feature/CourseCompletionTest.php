<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\UserCourseEnrollment;
use App\Models\UserCourseProgress;
use App\Http\Controllers\ProgressController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_not_completed_with_only_chapters_done()
    {
        // Create test data
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $chapters = Chapter::factory()->count(3)->create(['course_id' => $course->id]);
        
        $enrollment = UserCourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'payment_status' => 'paid',
            'final_exam_completed' => false, // Final exam NOT completed
        ]);

        // Complete all chapters
        foreach ($chapters as $chapter) {
            UserCourseProgress::create([
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapter->id,
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        // Update enrollment progress
        $progressController = new ProgressController();
        $progressController->updateEnrollmentProgressPublic($enrollment);

        // Refresh enrollment to get updated values
        $enrollment->refresh();

        // Assert course is NOT completed (should be 95% max)
        $this->assertNotEquals('completed', $enrollment->status);
        $this->assertLessThanOrEqual(95, $enrollment->progress_percentage);
        $this->assertNull($enrollment->completed_at);
    }

    public function test_course_completed_with_chapters_and_final_exam_done()
    {
        // Create test data
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $chapters = Chapter::factory()->count(3)->create(['course_id' => $course->id]);
        
        $enrollment = UserCourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'payment_status' => 'paid',
            'final_exam_completed' => true, // Final exam IS completed
        ]);

        // Complete all chapters
        foreach ($chapters as $chapter) {
            UserCourseProgress::create([
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapter->id,
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        // Update enrollment progress
        $progressController = new ProgressController();
        $progressController->updateEnrollmentProgressPublic($enrollment);

        // Refresh enrollment to get updated values
        $enrollment->refresh();

        // Assert course IS completed (should be 100%)
        $this->assertEquals('completed', $enrollment->status);
        $this->assertEquals(100, $enrollment->progress_percentage);
        $this->assertNotNull($enrollment->completed_at);
    }

    public function test_course_not_completed_with_final_exam_but_missing_chapters()
    {
        // Create test data
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $chapters = Chapter::factory()->count(3)->create(['course_id' => $course->id]);
        
        $enrollment = UserCourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'payment_status' => 'paid',
            'final_exam_completed' => true, // Final exam IS completed
        ]);

        // Complete only 2 out of 3 chapters
        for ($i = 0; $i < 2; $i++) {
            UserCourseProgress::create([
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapters[$i]->id,
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        // Update enrollment progress
        $progressController = new ProgressController();
        $progressController->updateEnrollmentProgressPublic($enrollment);

        // Refresh enrollment to get updated values
        $enrollment->refresh();

        // Assert course is NOT completed (should be around 67%)
        $this->assertNotEquals('completed', $enrollment->status);
        $this->assertLessThan(100, $enrollment->progress_percentage);
        $this->assertNull($enrollment->completed_at);
    }
}