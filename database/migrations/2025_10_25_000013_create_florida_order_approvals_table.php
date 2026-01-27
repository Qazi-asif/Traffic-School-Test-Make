<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create only if not already existing
        if (! Schema::hasTable('florida_order_approvals')) {
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

            // Optional data consistency constraint (only supported in MySQL 8+ / PostgreSQL)
            try {
                DB::statement('
                    ALTER TABLE florida_order_approvals 
                    ADD CONSTRAINT chk_florida_approval_consistency 
                    CHECK (
                        (approved_by_florida = 1 AND florida_approval_date IS NOT NULL)
                        OR (approved_by_florida = 0 AND florida_approval_date IS NULL)
                    )
                ');
            } catch (\Exception $e) {
                // Ignore if database doesn’t support check constraints
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_order_approvals');
    }
};
