<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('florida_courses')) {
            Schema::create('florida_courses', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('state', 50)->nullable();
                $table->integer('duration')->default(0);
                $table->decimal('price', 8, 2)->default(0);
                $table->integer('passing_score')->default(80);
                $table->boolean('is_active')->default(true);
                $table->string('course_type')->nullable();
                $table->string('certificate_type')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('florida_courses');
    }
};
