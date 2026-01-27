<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('florida_courses')) {
            Schema::create('florida_courses', function (Blueprint $table) {
                $table->id();

                // Course classification
                $table->enum('course_type', ['BDI', 'ADI', 'TLSAE'])->nullable()->default('BDI');
                $table->enum('delivery_type', ['internet', 'in_person', 'cd_rom', 'video', 'dvd'])->nullable()->default('internet');

                // Basic details
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('state_code')->nullable()->default('FL');

                // Duration and scoring
                $table->integer('min_pass_score')->nullable()->default(80);
                $table->integer('total_duration')->nullable()->default(240);
                $table->integer('duration_hours')->nullable()->default(4);

                // Pricing
                $table->decimal('price', 10, 2)->nullable()->default(0);

                // Integration and template info
                $table->string('dicds_course_id')->nullable();
                $table->string('certificate_template')->nullable();

                // Flags
                $table->boolean('is_active')->nullable()->default(true);
                $table->boolean('copyright_protected')->nullable()->default(true);

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_courses');
    }
};
