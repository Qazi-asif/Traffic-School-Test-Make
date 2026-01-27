<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_code_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_code_id')->constrained('court_codes')->onDelete('cascade');
            $table->enum('external_system', ['flhsmv', 'dicds', 'dmv', 'state_portal', 'other']);
            $table->string('external_code', 100);
            $table->string('external_name')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['court_code_id', 'external_system']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_code_mappings');
    }
};
