<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transmission_error_codes', function (Blueprint $table) {
            $table->id();
            $table->string('state', 2)->index();
            $table->string('error_code')->index();
            $table->string('error_category')->nullable();
            $table->text('technical_message');
            $table->text('user_friendly_message');
            $table->text('resolution_steps')->nullable();
            $table->boolean('is_retryable')->default(true);
            $table->timestamps();

            $table->unique(['state', 'error_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transmission_error_codes');
    }
};
