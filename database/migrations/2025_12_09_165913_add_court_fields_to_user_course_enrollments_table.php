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
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->string('case_number')->nullable()->after('citation_number');
            $table->string('court_state')->nullable()->after('case_number');
            $table->string('court_county')->nullable()->after('court_state');
            $table->string('court_selected')->nullable()->after('court_county');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['case_number', 'court_state', 'court_county', 'court_selected']);
        });
    }
};
