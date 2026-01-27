<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dicds_order_receipts')) {
            Schema::create('dicds_order_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('dicds_certificate_orders')->onDelete('cascade');
                $table->string('receipt_number')->unique();
                $table->json('receipt_data');
                $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
                $table->timestamp('generated_at');
                $table->timestamp('printed_at')->nullable();
                $table->timestamps();
            });

            // Add check constraint for logical consistency (MySQL 8+ / PostgreSQL)
            try {
                DB::statement('
                    ALTER TABLE dicds_order_receipts
                    ADD CONSTRAINT chk_receipt_timestamps
                    CHECK (
                        (printed_at IS NULL OR printed_at >= generated_at)
                    )
                ');
            } catch (\Exception $e) {
                // Ignore if the DB engine doesn’t support CHECK constraints
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dicds_order_receipts');
    }
};
