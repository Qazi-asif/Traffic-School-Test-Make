<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('dicds_certificate_orders')->onDelete('cascade');
            $table->boolean('approved_by_florida')->default(false);
            $table->date('florida_approval_date')->nullable();
            $table->string('florida_reference_number')->nullable();
            $table->boolean('certificate_numbers_released')->default(false);
            $table->timestamp('release_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_order_approvals');
    }
};
