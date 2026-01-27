<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create table if it doesn't exist
        if (! Schema::hasTable('dicds_certificate_orders')) {
            Schema::create('dicds_certificate_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('florida_schools')->onDelete('cascade');
                $table->foreignId('course_id')->constrained('florida_courses')->onDelete('cascade');
                $table->integer('certificate_count');
                $table->decimal('total_amount', 8, 2);
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dicds_certificate_orders');
    }
};
