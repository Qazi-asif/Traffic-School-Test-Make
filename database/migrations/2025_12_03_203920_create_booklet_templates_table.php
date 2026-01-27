<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booklet_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['cover', 'toc', 'chapter', 'quiz', 'certificate', 'footer']);
            $table->longText('content'); // HTML/Blade template
            $table->text('css')->nullable();
            $table->json('variables')->nullable(); // Available template variables
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booklet_templates');
    }
};
