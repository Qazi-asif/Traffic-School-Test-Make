<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_code_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_code_id')->constrained('court_codes')->onDelete('cascade');
            $table->enum('action', ['created', 'updated', 'deactivated', 'reactivated']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_code_history');
    }
};
