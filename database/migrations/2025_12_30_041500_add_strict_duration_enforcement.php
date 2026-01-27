<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'strict_duration_enabled')) {
                $table->boolean('strict_duration_enabled')->default(false)->after('is_active');
            }
        });

        Schema::table('florida_courses', function (Blueprint $table) {
            if (!Schema::hasColumn('florida_courses', 'strict_duration_enabled')) {
                $table->boolean('strict_duration_enabled')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'strict_duration_enabled')) {
                $table->dropColumn('strict_duration_enabled');
            }
        });

        Schema::table('florida_courses', function (Blueprint $table) {
            if (Schema::hasColumn('florida_courses', 'strict_duration_enabled')) {
                $table->dropColumn('strict_duration_enabled');
            }
        });
    }
};
