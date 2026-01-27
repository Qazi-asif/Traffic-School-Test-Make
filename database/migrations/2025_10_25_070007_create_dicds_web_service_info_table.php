<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dicds_web_service_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('florida_schools')->onDelete('cascade');
            $table->json('course_assignments');
            $table->json('instructor_assignments');
            $table->timestamp('last_updated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dicds_web_service_info');
    }
};
