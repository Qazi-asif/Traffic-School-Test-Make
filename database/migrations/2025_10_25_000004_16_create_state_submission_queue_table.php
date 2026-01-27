<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Prevents "table already exists" SQL error
        if (! Schema::hasTable('state_submission_queue')) {
            Schema::create('state_submission_queue', function (Blueprint $table) {
                $table->id();
                $table->foreignId('certificate_id')->constrained()->onDelete('cascade');
                $table->foreignId('state_config_id')->constrained('state_configurations')->onDelete('cascade');
                $table->enum('priority', ['high', 'normal', 'low'])->default('normal');
                $table->integer('attempts')->default(0);
                $table->integer('max_attempts')->default(3);
                $table->timestamp('last_attempt_at')->nullable();
                $table->timestamp('next_attempt_at')->nullable();
                $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'retry'])->default('pending');
                $table->text('error_message')->nullable();
                $table->json('submitted_data')->nullable();
                $table->json('response_data')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('state_submission_queue');
    }
};
