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
        Schema::create('certificate_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_id');
            $table->string('certificate_type'); // Model class name
            $table->string('verified_by'); // IP address or user ID
            $table->string('verification_method')->default('web'); // web, api, phone, etc.
            $table->string('verification_result')->default('valid'); // valid, invalid, expired
            $table->json('verification_data')->nullable(); // Additional verification details
            $table->timestamp('verified_at');
            $table->timestamps();

            $table->index(['certificate_id', 'certificate_type']);
            $table->index(['verified_by', 'verified_at']);
            $table->index(['verification_result', 'verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_verification_logs');
    }
};