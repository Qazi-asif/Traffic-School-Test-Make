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
        Schema::table('chapters', function (Blueprint $table) {
            if (!Schema::hasColumn('chapters', 'duration')) {
                $table->integer('duration')->default(30)->after('content');
            }
            if (!Schema::hasColumn('chapters', 'required_min_time')) {
                $table->integer('required_min_time')->default(30)->after('duration');
            }
            if (!Schema::hasColumn('chapters', 'course_table')) {
                $table->string('course_table')->default('florida_courses')->after('course_id');
            }
            if (!Schema::hasColumn('chapters', 'order_index')) {
                $table->integer('order_index')->default(1)->after('course_table');
            }
            if (!Schema::hasColumn('chapters', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('order_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn(['duration', 'required_min_time', 'course_table', 'order_index', 'is_active']);
        });
    }
};