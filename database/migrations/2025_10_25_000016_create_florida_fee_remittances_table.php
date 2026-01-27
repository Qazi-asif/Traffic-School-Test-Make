<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_fee_remittances', function (Blueprint $table) {
            $table->id();
            $table->date('remittance_date');
            $table->decimal('total_assessment_fees', 8, 2);
            $table->integer('total_courses');
            $table->enum('payment_method', ['check', 'electronic', 'money_order']);
            $table->string('florida_reference_number')->nullable();
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at');
            $table->boolean('processed_by_florida')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_fee_remittances');
    }
};
