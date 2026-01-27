<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nevada_compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->nullable()->constrained('user_course_enrollments')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('log_type', [
                'login', 'chapter_start', 'chapter_complete', 'quiz_attempt',
                'quiz_pass', 'quiz_fail', 'timeout', 'completion', 'certificate', 'submission',
            ]);
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->onDelete('set null');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('enrollment_id');
            $table->index('user_id');
            $table->index('log_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nevada_compliance_logs');
    }
};
