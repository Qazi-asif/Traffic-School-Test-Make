<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Prevent "Base table or view already exists" error
        if (! Schema::hasTable('course_media')) {
            Schema::create('course_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->foreignId('chapter_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('title');
                $table->enum('type', ['video', 'document', 'image']);
                $table->string('file_path');
                $table->string('file_name');
                $table->integer('file_size');
                $table->string('mime_type');
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_media');
    }
};
