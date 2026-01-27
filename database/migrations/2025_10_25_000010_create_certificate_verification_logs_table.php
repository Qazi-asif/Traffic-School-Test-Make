<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create the table if it doesn't exist
        if (! Schema::hasTable('certificate_verification_logs')) {
            Schema::create('certificate_verification_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('certificate_id')->constrained('florida_certificates')->onDelete('cascade');
                $table->string('verified_by')->nullable();
                $table->string('ip_address');
                $table->text('user_agent');
                $table->timestamp('verified_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_verification_logs');
    }
};
