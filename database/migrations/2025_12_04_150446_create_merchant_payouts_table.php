<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_account_id')->constrained('merchant_accounts')->onDelete('cascade');
            $table->string('payout_reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'in_transit', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('initiated_at');
            $table->date('expected_arrival_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->string('bank_account_last4', 4)->nullable();
            $table->string('failure_reason')->nullable();
            $table->json('transaction_ids')->nullable();
            $table->timestamps();

            $table->index(['merchant_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_payouts');
    }
};
