<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dicds_order_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('dicds_certificate_orders')->onDelete('cascade');
            $table->integer('original_certificate_count');
            $table->integer('amended_certificate_count');
            $table->decimal('original_total_amount', 8, 2);
            $table->decimal('amended_total_amount', 8, 2);
            $table->foreignId('amended_by')->constrained('users')->onDelete('cascade');
            $table->text('amendment_reason')->nullable();
            $table->timestamp('amended_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dicds_order_amendments');
    }
};
