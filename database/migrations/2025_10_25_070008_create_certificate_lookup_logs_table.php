<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_lookup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('searched_by')->constrained('users')->onDelete('cascade');
            $table->enum('search_type', ['certificate_number', 'student_name']);
            $table->string('search_term');
            $table->integer('results_count');
            $table->boolean('certificate_reprinted')->default(false);
            $table->timestamp('searched_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_lookup_logs');
    }
};
