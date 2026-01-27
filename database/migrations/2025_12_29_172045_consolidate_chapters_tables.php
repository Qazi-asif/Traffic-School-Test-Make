<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, let's add any missing columns to the chapters table to accommodate course_chapters data
        Schema::table('chapters', function (Blueprint $table) {
            if (!Schema::hasColumn('chapters', 'required_min_time')) {
                $table->integer('required_min_time')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('chapters', 'course_table')) {
                $table->string('course_table')->default('courses')->after('course_id');
            }
        });

        // Drop foreign key constraints that reference course_chapters
        $this->dropForeignKeyConstraints();

        // Migrate data from course_chapters to chapters table
        $this->migrateChapterData();

        // Update references in other tables
        $this->updateReferences();

        // Drop the course_chapters table
        Schema::dropIfExists('course_chapters');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Drop foreign key constraints that reference course_chapters
     */
    private function dropForeignKeyConstraints(): void
    {
        // Drop foreign key constraints
        try {
            Schema::table('user_course_progress', function (Blueprint $table) {
                $table->dropForeign('user_course_progress_chapter_id_foreign');
            });
        } catch (\Exception $e) {
            // Foreign key might not exist
        }

        try {
            Schema::table('chapter_progress', function (Blueprint $table) {
                $table->dropForeign(['chapter_id']);
            });
        } catch (\Exception $e) {
            // Foreign key might not exist
        }

        try {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropForeign(['chapter_id']);
            });
        } catch (\Exception $e) {
            // Foreign key might not exist
        }

        try {
            Schema::table('chapter_questions', function (Blueprint $table) {
                $table->dropForeign('chapter_questions_chapter_id_foreign');
            });
        } catch (\Exception $e) {
            // Foreign key might not exist
        }

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    }

    /**
     * Migrate data from course_chapters to chapters
     */
    private function migrateChapterData(): void
    {
        $courseChapters = DB::table('course_chapters')->get();
        
        foreach ($courseChapters as $courseChapter) {
            // Check if a similar chapter already exists in chapters table
            $existingChapter = DB::table('chapters')
                ->where('course_id', $courseChapter->course_id)
                ->where('title', $courseChapter->title)
                ->where('order_index', $courseChapter->order_index)
                ->first();
            
            if (!$existingChapter) {
                // Insert the chapter from course_chapters into chapters table
                DB::table('chapters')->insert([
                    'course_id' => $courseChapter->course_id,
                    'title' => $courseChapter->title,
                    'content' => $courseChapter->content ?? 'Chapter content',
                    'video_url' => $courseChapter->video_url,
                    'duration' => $courseChapter->duration ?? 60,
                    'required_min_time' => $courseChapter->required_min_time ?? $courseChapter->duration ?? 60,
                    'order_index' => $courseChapter->order_index,
                    'is_active' => $courseChapter->is_active ?? true,
                    'course_table' => 'florida_courses', // Assume course_chapters were for Florida courses
                    'created_at' => $courseChapter->created_at ?? now(),
                    'updated_at' => $courseChapter->updated_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Update references in other tables
     */
    private function updateReferences(): void
    {
        // Create a mapping from old course_chapter IDs to new chapter IDs
        $idMapping = [];
        $courseChapters = DB::table('course_chapters')->get();
        
        foreach ($courseChapters as $courseChapter) {
            $chapter = DB::table('chapters')
                ->where('course_id', $courseChapter->course_id)
                ->where('title', $courseChapter->title)
                ->where('order_index', $courseChapter->order_index)
                ->where('course_table', 'florida_courses')
                ->first();
            
            if ($chapter) {
                $idMapping[$courseChapter->id] = $chapter->id;
            }
        }

        // Update user_course_progress table
        foreach ($idMapping as $oldId => $newId) {
            DB::table('user_course_progress')
                ->where('chapter_id', $oldId)
                ->update(['chapter_id' => $newId]);
        }

        // Update chapter_progress table
        foreach ($idMapping as $oldId => $newId) {
            DB::table('chapter_progress')
                ->where('chapter_id', $oldId)
                ->update(['chapter_id' => $newId]);
        }

        // Update questions table
        foreach ($idMapping as $oldId => $newId) {
            DB::table('questions')
                ->where('chapter_id', $oldId)
                ->update(['chapter_id' => $newId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate course_chapters table
        Schema::create('course_chapters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('duration')->default(60);
            $table->integer('required_min_time')->nullable();
            $table->integer('order_index')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Remove added columns from chapters table
        Schema::table('chapters', function (Blueprint $table) {
            if (Schema::hasColumn('chapters', 'required_min_time')) {
                $table->dropColumn('required_min_time');
            }
            if (Schema::hasColumn('chapters', 'course_table')) {
                $table->dropColumn('course_table');
            }
        });
    }
};