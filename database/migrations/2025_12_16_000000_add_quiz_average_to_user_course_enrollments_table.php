<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->decimal('quiz_average', 5, 2)->nullable()->after('progress_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropColumn('quiz_average');
        });
    }
};