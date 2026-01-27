<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_mobile_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('device_type', ['desktop', 'tablet', 'mobile']);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('action');
            $table->json('mobile_performance_metric');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_mobile_analytics');
    }
};
