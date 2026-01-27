<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'title')) {
                $table->string('title')->after('id');
            }
            if (! Schema::hasColumn('courses', 'state')) {
                $table->string('state', 50)->nullable()->after('description');
            }
            if (! Schema::hasColumn('courses', 'duration')) {
                $table->integer('duration')->default(0)->after('state');
            }
            if (! Schema::hasColumn('courses', 'price')) {
                $table->decimal('price', 8, 2)->default(0)->after('duration');
            }
            if (! Schema::hasColumn('courses', 'passing_score')) {
                $table->integer('passing_score')->default(80)->after('price');
            }
            if (! Schema::hasColumn('courses', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('passing_score');
            }
            if (! Schema::hasColumn('courses', 'certificate_type')) {
                $table->string('certificate_type')->nullable()->after('course_type');
            }
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['title', 'state', 'duration', 'price', 'passing_score', 'is_active', 'certificate_type']);
        });
    }
};
