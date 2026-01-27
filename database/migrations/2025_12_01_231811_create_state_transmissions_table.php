<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('state_transmissions', function (Blueprint $table) {
            $table->id();

            // Foreign key to enrollment
            $table->foreignId('enrollment_id')
                ->constrained('user_course_enrollments')
                ->onDelete('cascade');

            // State information
            $table->string('state', 2)->index(); // FL, MO, TX, DE

            // Transmission status
            $table->enum('status', ['pending', 'success', 'error'])
                ->default('pending')
                ->index();

            // Payload and response data
            $table->json('payload_json')->nullable();
            $table->string('response_code')->nullable();
            $table->text('response_message')->nullable();

            // Timing and retry information
            $table->timestamp('sent_at')->nullable();
            $table->integer('retry_count')->default(0);

            $table->timestamps();

            // Indexes for performance
            $table->index(['state', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_transmissions');
    }
};
