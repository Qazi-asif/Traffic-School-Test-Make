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
            $table->json('optional_services')->nullable()->after('reminder_count');
            $table->decimal('optional_services_total', 8, 2)->default(0)->after('optional_services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['optional_services', 'optional_services_total']);
        });
    }
};