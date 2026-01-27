<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('enrollment_id')->nullable()->constrained('user_course_enrollments')->onDelete('set null');
                $table->enum('gateway', ['stripe', 'paypal']);
                $table->string('gateway_payment_id')->unique();
                $table->string('intent_id')->nullable();
                $table->decimal('amount', 8, 2);
                $table->string('currency', 3)->default('usd');
                $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'disputed']);
                $table->string('payment_method')->nullable();
                $table->string('billing_name');
                $table->string('billing_email');
                $table->json('billing_address')->nullable();
                $table->text('refund_reason')->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
