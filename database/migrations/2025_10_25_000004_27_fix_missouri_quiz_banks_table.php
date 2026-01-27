<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the old 'chapter' column if it exists
        if (Schema::hasColumn('missouri_quiz_banks', 'chapter')) {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->dropColumn('chapter');
            });
        }

        // Drop old foreign key constraint safely (MySQL syntax)
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'missouri_quiz_banks' 
            AND CONSTRAINT_SCHEMA = DATABASE() 
            AND COLUMN_NAME = 'chapter_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        if (! empty($foreignKeys)) {
            $fkName = $foreignKeys[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE missouri_quiz_banks DROP FOREIGN KEY `$fkName`");
        }

        // Make chapter_id nullable
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable()->change();
        });

        // Add option_e if not exists
        if (! Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->string('option_e')->nullable()->after('option_d');
            });
        }

        // Drop and re-add correct_answer CHECK constraint (MySQL 8+)
        $checkConstraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_NAME = 'missouri_quiz_banks' 
            AND CONSTRAINT_TYPE = 'CHECK'
            AND CONSTRAINT_SCHEMA = DATABASE()
        ");
        foreach ($checkConstraints as $constraint) {
            DB::statement("ALTER TABLE missouri_quiz_banks DROP CHECK `$constraint->CONSTRAINT_NAME`");
        }

        DB::statement("
            ALTER TABLE missouri_quiz_banks 
            ADD CONSTRAINT missouri_quiz_banks_correct_answer_check 
            CHECK (correct_answer IN ('A', 'B', 'C', 'D', 'E'))
        ");

        // Add is_final_exam if not exists
        if (! Schema::hasColumn('missouri_quiz_banks', 'is_final_exam')) {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->boolean('is_final_exam')->default(false)->after('state_required');
            });
        }

        // Drop category column if exists
        if (Schema::hasColumn('missouri_quiz_banks', 'category')) {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }

        // Re-add foreign key constraint properly
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            $table->foreign('chapter_id')
                ->references('id')
                ->on('missouri_course_structures')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        // Drop FK first
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'missouri_quiz_banks' 
            AND CONSTRAINT_SCHEMA = DATABASE() 
            AND COLUMN_NAME = 'chapter_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        if (! empty($foreignKeys)) {
            $fkName = $foreignKeys[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE missouri_quiz_banks DROP FOREIGN KEY `$fkName`");
        }

        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            if (Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
                $table->dropColumn('option_e');
            }
            if (Schema::hasColumn('missouri_quiz_banks', 'is_final_exam')) {
                $table->dropColumn('is_final_exam');
            }
            if (! Schema::hasColumn('missouri_quiz_banks', 'chapter')) {
                $table->integer('chapter')->nullable();
            }
            if (! Schema::hasColumn('missouri_quiz_banks', 'category')) {
                $table->enum('category', [
                    'traffic_laws',
                    'road_signs',
                    'safe_driving',
                    'alcohol_drugs',
                    'defensive_driving',
                ])->after('correct_answer');
            }
        });

        // Drop and reset CHECK constraint
        $checkConstraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_NAME = 'missouri_quiz_banks' 
            AND CONSTRAINT_TYPE = 'CHECK'
            AND CONSTRAINT_SCHEMA = DATABASE()
        ");
        foreach ($checkConstraints as $constraint) {
            DB::statement("ALTER TABLE missouri_quiz_banks DROP CHECK `$constraint->CONSTRAINT_NAME`");
        }

        DB::statement("
            ALTER TABLE missouri_quiz_banks 
            ADD CONSTRAINT missouri_quiz_banks_correct_answer_check 
            CHECK (correct_answer IN ('A', 'B', 'C', 'D'))
        ");

        // Re-add foreign key
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            $table->foreign('chapter_id')
                ->references('id')
                ->on('missouri_course_structures')
                ->onDelete('cascade');
        });
    }
};
