<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('free_response_quiz_placements', function (Blueprint $table) {
            // Add random selection fields
            $table->boolean('use_random_selection')->default(false)->after('is_mandatory');
            $table->integer('questions_to_select')->nullable()->after('use_random_selection')
                ->comment('Number of questions to randomly select from pool');
            $table->integer('total_questions_in_pool')->nullable()->after('questions_to_select')
                ->comment('Total questions available in pool (for reference)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('free_response_quiz_placements', function (Blueprint $table) {
            $table->dropColumn(['use_random_selection', 'questions_to_select', 'total_questions_in_pool']);
        });
    }
};
