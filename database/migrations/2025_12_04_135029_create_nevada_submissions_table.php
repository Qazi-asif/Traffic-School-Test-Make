<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nevada_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('nevada_certificates')->onDelete('cascade');
            $table->enum('submission_method', ['electronic', 'mail', 'fax']);
            $table->timestamp('submission_date');
            $table->enum('status', ['pending', 'sent', 'confirmed', 'failed'])->default('pending');
            $table->string('confirmation_number')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('certificate_id');
            $table->index('status');
            $table->index('submission_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nevada_submissions');
    }
};
