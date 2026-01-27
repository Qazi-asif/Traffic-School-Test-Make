<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create table if it doesn't exist
        if (! Schema::hasTable('certificate_distribution')) {
            Schema::create('certificate_distribution', function (Blueprint $table) {
                $table->id();
                $table->foreignId('certificate_order_id')->constrained('dicds_certificate_orders')->onDelete('cascade');
                $table->foreignId('florida_school_id')->constrained('florida_schools')->onDelete('cascade');
                $table->enum('course_type', ['BDI', 'ADI', 'TLSAE']);
                $table->integer('amount_distributed');
                $table->foreignId('distributed_by')->constrained('users')->onDelete('cascade');
                $table->timestamp('distributed_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_distribution');
    }
};
