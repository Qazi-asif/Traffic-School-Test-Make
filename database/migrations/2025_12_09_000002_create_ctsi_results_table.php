<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctsi_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->string('key_response')->nullable();
            $table->text('save_data')->nullable();
            $table->timestamp('process_date')->nullable();
            $table->longText('raw_xml')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamps();

            $table->index('enrollment_id');
            $table->index('status');
            $table->index('process_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctsi_results');
    }
};
