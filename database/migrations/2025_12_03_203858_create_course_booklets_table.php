<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_booklets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('version'); // e.g., "2025.1"
            $table->string('title');
            $table->string('state_code', 2)->nullable(); // State-specific version
            $table->string('file_path'); // Stored PDF path
            $table->integer('page_count')->default(0);
            $table->integer('file_size')->default(0); // bytes
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_booklets');
    }
};
