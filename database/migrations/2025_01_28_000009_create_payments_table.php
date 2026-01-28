<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('course_id');
            $table->string('course_table')->default('courses'); // florida_courses, missouri_courses, etc.
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['stripe', 'paypal', 'authorizenet'])->default('stripe');
            
            // Gateway-specific fields
            $table->string('gateway_payment_id')->nullable(); // Stripe PaymentIntent ID, PayPal Payment ID, etc.
            $table->string('gateway_customer_id')->nullable(); // Stripe Customer ID
            $table->string('gateway_transaction_id')->nullable(); // Final transaction ID after completion
            $table->decimal('gateway_fee', 8, 2)->default(0); // Gateway processing fee
            
            // Additional data
            $table->json('metadata')->nullable(); // Store additional payment data
            $table->timestamp('paid_at')->nullable();
            $table->text('error_message')->nullable();
            
            // Refund fields
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['course_id', 'course_table']);
            $table->index(['gateway_payment_id']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_method', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};