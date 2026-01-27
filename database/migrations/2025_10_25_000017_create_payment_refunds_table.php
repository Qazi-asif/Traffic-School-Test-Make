<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_refunds')) {
            Schema::create('payment_refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('florida_payments')->onDelete('cascade');
                $table->decimal('refund_amount', 8, 2);
                $table->enum('refund_reason', ['student_request', 'course_not_completed', 'system_error', 'duplicate_charge']);
                $table->text('refund_description')->nullable();
                $table->string('gateway_refund_id')->nullable();
                $table->enum('status', ['pending', 'processed', 'failed']);
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('processed_at')->nullable();
                $table->boolean('florida_fee_refunded')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};
