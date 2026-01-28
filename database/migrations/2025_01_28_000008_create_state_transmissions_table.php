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
            $table->unsignedBigInteger('certificate_id')->nullable();
            $table->unsignedBigInteger('enrollment_id');
            $table->string('state', 2); // FL, MO, TX, DE
            $table->string('system'); // DICDS, DOR, TDLR, DMV
            $table->string('status')->default('pending'); // pending, processing, success, error, failed
            $table->json('payload_json'); // Request payload
            $table->string('response_code')->nullable();
            $table->text('response_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->foreign('certificate_id')->references('id')->on('certificates')->onDelete('set null');
            $table->foreign('enrollment_id')->references('id')->on('user_course_enrollments')->onDelete('cascade');
            
            $table->index(['state', 'status', 'created_at']);
            $table->index(['system', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['retry_count', 'status']);
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