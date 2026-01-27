<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booklet_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->foreignId('booklet_id')->constrained('course_booklets')->onDelete('cascade');
            $table->enum('status', ['pending', 'generating', 'ready', 'printed', 'shipped', 'delivered', 'failed'])->default('pending');
            $table->enum('format', ['pdf_download', 'print_mail', 'print_pickup'])->default('pdf_download');
            $table->string('file_path')->nullable(); // Generated personalized booklet
            $table->json('personalization_data')->nullable(); // Student name, etc.
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booklet_orders');
    }
};
