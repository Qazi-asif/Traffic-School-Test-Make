<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gateway_id')->constrained('payment_gateways')->onDelete('cascade');
            $table->string('setting_key');
            $table->text('setting_value');
            $table->boolean('is_sensitive')->default(false);
            $table->enum('environment', ['test', 'production']);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['gateway_id', 'setting_key', 'environment'], 'pg_settings_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};
