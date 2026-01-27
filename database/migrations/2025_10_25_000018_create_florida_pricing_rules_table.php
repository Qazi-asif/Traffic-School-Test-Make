<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->enum('course_type', ['BDI', 'ADI', 'TLSAE']);
            $table->enum('delivery_type', ['internet', 'in_person', 'cd_rom', 'video', 'dvd']);
            $table->decimal('base_price', 8, 2);
            $table->decimal('florida_assessment_fee', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_pricing_rules');
    }
};
