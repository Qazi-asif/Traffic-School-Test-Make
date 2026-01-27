<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_course_enrollments')) {
            Schema::create('user_course_enrollments', function (Blueprint $table) {
                $table->id();

                // Foreign keys
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('course_id')->constrained('florida_courses')->onDelete('cascade');

                // Enrollment and progress
                $table->enum('status', ['enrolled', 'in_progress', 'completed', 'expired', 'active', 'cancelled'])
                    ->nullable()
                    ->default('enrolled');
                $table->timestamp('enrolled_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->decimal('progress_percentage', 5, 2)->nullable()->default(0);
                $table->integer('total_time_spent')->nullable()->default(0);

                // Payment details
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                    ->nullable()
                    ->default('pending');
                $table->decimal('amount_paid', 8, 2)->nullable()->default(0);
                $table->string('payment_method')->nullable();
                $table->string('payment_id')->nullable();

                // Additional fields
                $table->string('citation_number')->nullable();
                $table->date('court_date')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_course_enrollments');
    }
};
