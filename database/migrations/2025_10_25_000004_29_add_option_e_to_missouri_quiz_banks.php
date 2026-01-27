<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip if table doesn't exist
        if (! Schema::hasTable('missouri_quiz_banks')) {
            return;
        }

        // Add option_e if it doesn't exist
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            if (! Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
                $table->string('option_e')->nullable()->after('option_d');
            }
        });

        // Note: CHECK constraints removed for MariaDB compatibility
        // The correct_answer enum will still enforce valid values at the application level
    }

    public function down()
    {
        // Skip if table doesn't exist
        if (! Schema::hasTable('missouri_quiz_banks')) {
            return;
        }

        // Drop option_e if exists
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            if (Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
                $table->dropColumn('option_e');
            }
        });
    }
};
