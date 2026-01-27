<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Prevent "table already exists" error
        if (! Schema::hasTable('refunds')) {
            Schema::create('refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained()->onDelete('cascade');
                $table->string('gateway_refund_id')->nullable();
                $table->decimal('amount', 8, 2);
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'completed', 'failed']);
                $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
