<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->nullable()->constrained('courts')->onDelete('cascade');
            $table->enum('code_type', ['tvcc', 'court_id', 'location_code', 'branch_code', 'state_code']);
            $table->string('code_value', 50);
            $table->string('code_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['code_value', 'code_type']);
            $table->index(['court_id', 'code_type']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_codes');
    }
};
