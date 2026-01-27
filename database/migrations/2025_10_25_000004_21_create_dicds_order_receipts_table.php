<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dicds_order_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('dicds_certificate_orders')->onDelete('cascade');
            $table->string('receipt_number')->unique();
            $table->json('receipt_data');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('generated_at');
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dicds_order_receipts');
    }
};
