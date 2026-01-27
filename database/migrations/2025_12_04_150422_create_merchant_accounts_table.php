<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gateway_id')->constrained('payment_gateways')->onDelete('cascade');
            $table->string('account_name');
            $table->string('account_identifier');
            $table->string('account_email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('currency', 3)->default('USD');
            $table->enum('payout_schedule', ['daily', 'weekly', 'monthly', 'manual'])->default('manual');
            $table->integer('payout_day')->nullable();
            $table->decimal('minimum_payout', 8, 2)->nullable();
            $table->decimal('reserve_percent', 5, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_payout_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_accounts');
    }
};
