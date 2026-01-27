<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create table if it does not exist
        if (! Schema::hasTable('florida_courses')) {
            Schema::create('florida_courses', function (Blueprint $table) {
                $table->id();
                $table->enum('course_type', ['BDI', 'ADI', 'TLSAE'])->default('BDI');
                $table->enum('delivery_type', ['internet', 'in_person', 'cd_rom', 'video', 'dvd'])->default('internet');
                $table->string('title');
                $table->text('description');
                $table->string('state_code')->default('FL');
                $table->integer('min_pass_score')->default(80);
                $table->integer('total_duration')->default(240);
                $table->decimal('price', 8, 2);
                $table->string('dicds_course_id');
                $table->string('certificate_template')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('copyright_protected')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_courses');
    }
};
