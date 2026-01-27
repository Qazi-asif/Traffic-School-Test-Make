<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\FloridaCourse;

class MigrateCoursesToFlorida extends Command
{
    protected $signature = 'courses:migrate-to-florida {--dry-run : Show what would be migrated without actually doing it}';
    protected $description = 'Migrate all courses from courses table to florida_courses table with all associated data';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->warn('⚠️  This will migrate all courses to florida_courses table');
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Migration cancelled.');
                return;
            }
        }

        try {
            DB::beginTransaction();

            // Get all courses from regular courses table
            $courses = Course::all();
            
            if ($courses->isEmpty()) {
                $this->info('No courses found in courses table to migrate.');
                return;
            }

            $this->info("Found {$courses->count()} courses to migrate:");
            
            $migratedCount = 0;
            $skippedCount = 0;

            foreach ($courses as $course) {
                $this->line("Processing: {$course->title} (ID: {$course->id})");
                
                // Check if course already exists in florida_courses
                $existingFlorida = FloridaCourse::where('title', $course->title)
                    ->where('state_code', $course->state_code)
                    ->first();
                
                if ($existingFlorida) {
                    $this->warn("  ⏭️  Skipped - Similar course already exists in florida_courses");
                    $skippedCount++;
                    continue;
                }

                if (!$dryRun) {
                    // Create new Florida course
                    $minPassScore = $course->min_pass_score ?? 80; // Default to 80 if null
                    $floridaCourse = FloridaCourse::create([
                        'course_type' => $this->mapCourseType($course->state_code),
                        'delivery_type' => 'internet',
                        'title' => $course->title,
                        'description' => $course->description,
                        'duration' => $course->total_duration ?? 240, // Default duration
                        'min_pass_score' => $minPassScore,
                        'passing_score' => $minPassScore,
                        'price' => $course->price ?? 0,
                        'dicds_course_id' => $this->generateDicdsId($course),
                        'is_active' => $course->is_active ?? true,
                        'state_code' => $course->state_code ?? 'FL',
                        'created_at' => $course->created_at ?? now(),
                        'updated_at' => $course->updated_at ?? now(),
                    ]);

                    // Migrate chapters
                    $chapters = DB::table('course_chapters')
                        ->where('course_id', $course->id)
                        ->orderBy('order_index')
                        ->get();

                    $chapterMapping = [];
                    foreach ($chapters as $chapter) {
                        $newChapterId = DB::table('course_chapters')->insertGetId([
                            'course_id' => $floridaCourse->id,
                            'title' => $chapter->title,
                            'content' => $chapter->content,
                            'duration' => $chapter->duration ?? 30,
                            'required_min_time' => $chapter->required_min_time ?? ($chapter->duration ?? 30),
                            'order_index' => $chapter->order_index ?? 1,
                            'video_url' => $chapter->video_url,
                            'is_active' => $chapter->is_active ?? true,
                            'created_at' => $chapter->created_at ?? now(),
                            'updated_at' => $chapter->updated_at ?? now(),
                        ]);
                        
                        $chapterMapping[$chapter->id] = $newChapterId;
                    }

                    // Migrate chapter questions
                    $questionCount = 0;
                    foreach ($chapterMapping as $oldChapterId => $newChapterId) {
                        $questions = DB::table('questions')
                            ->where('chapter_id', $oldChapterId)
                            ->get();

                        foreach ($questions as $question) {
                            DB::table('questions')->insert([
                                'chapter_id' => $newChapterId,
                                'course_id' => $floridaCourse->id,
                                'question_text' => $question->question_text,
                                'question_type' => $question->question_type,
                                'options' => $question->options,
                                'correct_answer' => $question->correct_answer,
                                'explanation' => $question->explanation,
                                'points' => $question->points,
                                'order_index' => $question->order_index,
                                'created_at' => $question->created_at,
                                'updated_at' => $question->updated_at,
                            ]);
                            $questionCount++;
                        }
                    }

                    // Migrate final exam questions
                    $finalExamQuestions = DB::table('final_exam_questions')
                        ->where('course_id', $course->id)
                        ->get();

                    $finalExamCount = 0;
                    foreach ($finalExamQuestions as $question) {
                        DB::table('final_exam_questions')->insert([
                            'course_id' => $floridaCourse->id,
                            'question_text' => $question->question_text,
                            'question_type' => $question->question_type,
                            'options' => $question->options,
                            'correct_answer' => $question->correct_answer,
                            'explanation' => $question->explanation,
                            'points' => $question->points,
                            'order_index' => $question->order_index,
                            'created_at' => $question->created_at,
                            'updated_at' => $question->updated_at,
                        ]);
                        $finalExamCount++;
                    }

                    // Update enrollments to point to new course
                    DB::table('user_course_enrollments')
                        ->where('course_id', $course->id)
                        ->where('course_table', 'courses')
                        ->update([
                            'course_id' => $floridaCourse->id,
                            'course_table' => 'florida_courses'
                        ]);

                    $this->info("  ✅ Migrated: {$chapters->count()} chapters, {$questionCount} questions, {$finalExamCount} final exam questions");
                } else {
                    // Dry run - just show what would be migrated
                    $chapters = DB::table('course_chapters')->where('course_id', $course->id)->count();
                    $questions = DB::table('questions')->where('course_id', $course->id)->count();
                    $finalExam = DB::table('final_exam_questions')->where('course_id', $course->id)->count();
                    $enrollments = DB::table('user_course_enrollments')
                        ->where('course_id', $course->id)
                        ->where('course_table', 'courses')
                        ->count();
                    
                    $this->info("  📋 Would migrate: {$chapters} chapters, {$questions} questions, {$finalExam} final exam questions, {$enrollments} enrollments");
                }

                $migratedCount++;
            }

            if (!$dryRun) {
                DB::commit();
                $this->info("\n🎉 Migration completed successfully!");
                $this->info("✅ Migrated: {$migratedCount} courses");
                $this->info("⏭️  Skipped: {$skippedCount} courses (already exist)");
                
                $this->warn("\n⚠️  IMPORTANT: After verifying the migration, you may want to:");
                $this->line("1. Backup and remove old courses from 'courses' table");
                $this->line("2. Update any hardcoded references to 'courses' table");
                $this->line("3. Test all functionality with the migrated data");
            } else {
                $this->info("\n📊 Dry run summary:");
                $this->info("Would migrate: {$migratedCount} courses");
                $this->info("Would skip: {$skippedCount} courses");
                $this->line("\nRun without --dry-run to perform actual migration");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Migration failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function mapCourseType($stateCode)
    {
        return match($stateCode) {
            'FL' => 'BDI',
            'CA' => 'BDI', 
            'TX' => 'DDC',
            'MO' => 'DDC',
            'NY' => 'DDC',
            default => 'BDI'
        };
    }

    private function generateDicdsId($course)
    {
        // Generate a unique DICDS ID based on course info
        $baseId = strtoupper(substr($course->state_code, 0, 2)) . '_' . 
                  str_replace(' ', '_', strtoupper(substr($course->title, 0, 10))) . '_' . 
                  $course->id;
        
        return substr($baseId, 0, 50); // Limit to 50 characters
    }
}