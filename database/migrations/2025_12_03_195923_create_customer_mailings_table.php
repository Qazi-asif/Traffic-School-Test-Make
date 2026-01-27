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
        Schema::create('customer_mailings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->foreignId('certificate_id')->nullable()->constrained('florida_certificates')->onDelete('set null');
            $table->enum('mailing_type', ['certificate_copy', 'welcome_kit', 'booklet', 'other'])->default('certificate_copy');
            $table->enum('status', ['pending', 'printed', 'mailed', 'delivered', 'returned'])->default('pending');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->string('tracking_number')->nullable();
            $table->enum('carrier', ['usps', 'fedex', 'ups', 'other'])->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('mailed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_mailings');
    }
};
