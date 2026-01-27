<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip if table doesn't exist
        if (! Schema::hasTable('missouri_quiz_banks')) {
            return;
        }

        // Drop columns safely
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            if (Schema::hasColumn('missouri_quiz_banks', 'chapter')) {
                $table->dropColumn('chapter');
            }
            if (Schema::hasColumn('missouri_quiz_banks', 'question')) {
                $table->dropColumn('question');
            }
            if (Schema::hasColumn('missouri_quiz_banks', 'difficulty')) {
                $table->dropColumn('difficulty');
            }
            if (Schema::hasColumn('missouri_quiz_banks', 'category')) {
                $table->dropColumn('category');
            }

            if (! Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
                $table->string('option_e')->nullable()->after('option_d');
            }
            if (! Schema::hasColumn('missouri_quiz_banks', 'is_final_exam')) {
                $table->boolean('is_final_exam')->default(false)->after('state_required');
            }
        });

        // Drop foreign key if exists
        try {
            $fk = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'missouri_quiz_banks' 
                  AND COLUMN_NAME = 'chapter_id' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            if (! empty($fk)) {
                DB::statement("ALTER TABLE missouri_quiz_banks DROP FOREIGN KEY `{$fk[0]->CONSTRAINT_NAME}`");
            }
        } catch (\Exception $e) {
            // Ignore if foreign key doesn't exist
        }

        // Make chapter_id nullable
        try {
            DB::statement('ALTER TABLE missouri_quiz_banks MODIFY chapter_id BIGINT UNSIGNED NULL');
        } catch (\Exception $e) {
            // Ignore if already nullable
        }

        // Re-add foreign key
        try {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->foreign('chapter_id')
                    ->references('id')
                    ->on('missouri_course_structures')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Ignore if foreign key already exists
        }
    }

    public function down()
    {
        // Reverse operations - simplified for MariaDB compatibility
        if (! Schema::hasTable('missouri_quiz_banks')) {
            return;
        }

        // Just drop the foreign key if it exists
        try {
            $fk = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'missouri_quiz_banks' 
                  AND COLUMN_NAME = 'chapter_id' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            if (! empty($fk)) {
                DB::statement("ALTER TABLE missouri_quiz_banks DROP FOREIGN KEY `{$fk[0]->CONSTRAINT_NAME}`");
            }
        } catch (\Exception $e) {
            // Ignore errors
        }
    }
};
