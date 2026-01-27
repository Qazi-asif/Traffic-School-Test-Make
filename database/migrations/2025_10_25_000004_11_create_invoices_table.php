<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Prevents "table already exists" error
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained()->onDelete('cascade');
                $table->string('invoice_number')->unique();
                $table->date('invoice_date');
                $table->date('due_date')->nullable();
                $table->json('items')->nullable();
                $table->decimal('subtotal', 8, 2)->nullable();
                $table->decimal('tax_amount', 8, 2)->default(0);
                $table->decimal('total_amount', 8, 2);
                $table->string('pdf_path')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
