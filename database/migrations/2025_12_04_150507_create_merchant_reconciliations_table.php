<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_account_id')->constrained('merchant_accounts')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('expected_revenue', 12, 2)->default(0);
            $table->decimal('actual_revenue', 12, 2)->default(0);
            $table->decimal('expected_fees', 10, 2)->default(0);
            $table->decimal('actual_fees', 10, 2)->default(0);
            $table->decimal('discrepancy_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'matched', 'discrepancy', 'resolved'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_reconciliations');
    }
};
