<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_course_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('florida_instructors')->onDelete('cascade');
            $table->enum('course_type', ['BDI', 'ADI', 'TLSAE']);
            $table->enum('delivery_type', ['internet', 'in_person', 'cd_rom', 'video', 'dvd']);
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->date('status_date');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_course_assignments');
    }
};
