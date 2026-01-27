<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('court_mailings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->foreignId('certificate_id')->nullable()->constrained('florida_certificates')->onDelete('set null');
            $table->foreignId('court_id')->nullable()->constrained('courts')->onDelete('set null');
            $table->enum('mailing_type', ['certificate', 'completion_notice', 'amendment', 'other'])->default('certificate');
            $table->enum('recipient_type', ['court', 'customer', 'both'])->default('court');
            $table->enum('status', ['pending', 'printed', 'mailed', 'delivered', 'returned', 'failed'])->default('pending');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->string('tracking_number')->nullable();
            $table->enum('carrier', ['usps', 'fedex', 'ups', 'other'])->nullable();
            $table->string('shipping_method')->nullable();
            $table->decimal('weight_oz', 8, 2)->nullable();
            $table->decimal('postage_cost', 8, 2)->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('mailed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('return_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('printed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('mailed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('batch_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_mailings');
    }
};
