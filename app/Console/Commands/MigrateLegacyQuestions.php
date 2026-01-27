<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use App\Models\ChapterQuestion;
use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiz:migrate-legacy-questions 
                            {--dry-run : Show what would be migrated without actually doing it}
                            {--chapter= : Migrate questions for a specific chapter ID only}
                            {--force : Force migration even if chapter_questions already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate questions from legacy questions table to chapter_questions table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Legacy Questions Migration Tool ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $specificChapter = $this->option('chapter');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Get chapters with legacy questions
        $query = DB::table('questions')
            ->select('chapter_id', DB::raw('COUNT(*) as question_count'))
            ->groupBy('chapter_id');

        if ($specificChapter) {
            $query->where('chapter_id', $specificChapter);
        }

        $chaptersWithLegacyQuestions = $query->get();

        if ($chaptersWithLegacyQuestions->isEmpty()) {
            $this->info('No legacy questions found to migrate.');
            return 0;
        }

        $this->info("Found {$chaptersWithLegacyQuestions->count()} chapters with legacy questions:");
        $this->newLine();

        $totalMigrated = 0;
        $totalSkipped = 0;

        foreach ($chaptersWithLegacyQuestions as $chapterData) {
            $chapterId = $chapterData->chapter_id;
            $legacyCount = $chapterData->question_count;

            $chapter = Chapter::find($chapterId);
            $chapterTitle = $chapter ? $chapter->title : "Chapter {$chapterId}";

            // Check if chapter_questions already exist
            $existingCount = ChapterQuestion::where('chapter_id', $chapterId)->count();

            $this->info("Chapter {$chapterId}: {$chapterTitle}");
            $this->line("  - Legacy questions: {$legacyCount}");
            $this->line("  - Existing chapter_questions: {$existingCount}");

            if ($existingCount > 0 && !$force) {
                $this->warn("  - SKIPPED: Chapter already has questions in chapter_questions table");
                $this->line("    Use --force to migrate anyway (will create duplicates)");
                $totalSkipped += $legacyCount;
                $this->newLine();
                continue;
            }

            if (!$dryRun) {
                // Migrate questions
                $legacyQuestions = Question::where('chapter_id', $chapterId)
                    ->orderBy('order_index')
                    ->get();

                $migrated = 0;
                foreach ($legacyQuestions as $legacyQuestion) {
                    try {
                        ChapterQuestion::create([
                            'chapter_id' => $legacyQuestion->chapter_id,
                            'question_text' => $legacyQuestion->question_text,
                            'question_type' => $legacyQuestion->question_type ?? 'multiple_choice',
                            'options' => $legacyQuestion->options,
                            'correct_answer' => $legacyQuestion->correct_answer,
                            'explanation' => $legacyQuestion->explanation,
                            'points' => $legacyQuestion->points ?? 1,
                            'order_index' => $legacyQuestion->order_index ?? 1,
                            'quiz_set' => $legacyQuestion->quiz_set ?? 1,
                        ]);
                        $migrated++;
                    } catch (\Exception $e) {
                        $this->error("    Failed to migrate question ID {$legacyQuestion->id}: {$e->getMessage()}");
                    }
                }

                $this->info("  - MIGRATED: {$migrated} questions");
                $totalMigrated += $migrated;
            } else {
                $this->info("  - WOULD MIGRATE: {$legacyCount} questions");
                $totalMigrated += $legacyCount;
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info('=== Migration Summary ===');
        
        if ($dryRun) {
            $this->info("Questions that would be migrated: {$totalMigrated}");
            $this->info("Questions that would be skipped: {$totalSkipped}");
            $this->newLine();
            $this->comment('Run without --dry-run to perform the actual migration');
        } else {
            $this->info("Questions migrated: {$totalMigrated}");
            $this->info("Questions skipped: {$totalSkipped}");
            $this->newLine();
            
            if ($totalMigrated > 0) {
                $this->comment('Migration completed successfully!');
                $this->comment('You can now set DISABLE_LEGACY_QUESTIONS_TABLE=true in your .env file');
                $this->comment('to prevent the system from using the legacy questions table.');
            }
        }

        return 0;
    }
}