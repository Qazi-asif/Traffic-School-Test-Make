<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_management_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('florida_schools')->onDelete('cascade');
            $table->enum('action', ['disabled', 'enabled', 'course_added', 'course_removed']);
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->json('details');
            $table->timestamp('performed_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_management_logs');
    }
};
