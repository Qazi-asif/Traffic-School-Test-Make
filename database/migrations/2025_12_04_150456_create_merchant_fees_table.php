<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_account_id')->constrained('merchant_accounts')->onDelete('cascade');
            $table->enum('fee_type', ['transaction', 'monthly', 'chargeback', 'refund', 'payout', 'other']);
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('gateway_fee_id')->nullable();
            $table->timestamps();

            $table->index(['merchant_account_id', 'fee_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_fees');
    }
};
