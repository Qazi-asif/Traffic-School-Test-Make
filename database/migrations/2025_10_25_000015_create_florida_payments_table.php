<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('florida_payments')) {
            Schema::create('florida_payments', function (Blueprint $table) {
                $table->id();

                // Relationships
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('enrollment_id')
                    ->nullable()
                    ->constrained('user_course_enrollments')
                    ->onDelete('set null');

                // Course + Payment Info
                $table->enum('course_type', ['BDI', 'ADI', 'TLSAE']);
                $table->enum('delivery_type', ['internet', 'in_person', 'cd_rom', 'video', 'dvd']);
                $table->decimal('base_course_price', 8, 2);
                $table->decimal('florida_assessment_fee', 8, 2);
                $table->decimal('convenience_fee', 8, 2)->default(0);
                $table->decimal('total_amount', 8, 2);

                // Gateway & Status
                $table->enum('payment_gateway', ['stripe', 'paypal']);
                $table->string('gateway_payment_id')->unique();
                $table->string('gateway_intent_id')->nullable();
                $table->enum('payment_status', [
                    'pending',
                    'processing',
                    'completed',
                    'failed',
                    'refunded',
                    'disputed',
                ])->default('pending');
                $table->string('payment_method')->nullable();

                // Billing Info
                $table->string('billing_name');
                $table->string('billing_email');
                $table->json('billing_address')->nullable();

                // Florida Fee / Refund Tracking
                $table->boolean('florida_fee_remitted')->default(false);
                $table->date('florida_fee_remittance_date')->nullable();
                $table->string('florida_remittance_reference')->nullable();
                $table->text('refund_reason')->nullable();
                $table->timestamp('refunded_at')->nullable();

                // Metadata
                $table->json('metadata')->nullable();

                // Timestamps
                $table->timestamps();
            });

            // Add CHECK constraint for refund logic
            try {
                DB::statement("
                    ALTER TABLE florida_payments
                    ADD CONSTRAINT chk_refund_consistency
                    CHECK (
                        (payment_status != 'refunded' AND refunded_at IS NULL)
                        OR
                        (payment_status = 'refunded' AND refunded_at IS NOT NULL)
                    )
                ");
            } catch (\Exception $e) {
                // MySQL < 8 doesn't support CHECK constraints
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_payments');
    }
};
