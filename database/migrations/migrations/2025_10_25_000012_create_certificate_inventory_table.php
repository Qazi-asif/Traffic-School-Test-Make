<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_inventory', function (Blueprint $table) {
            $table->id();
            $table->enum('course_type', ['BDI', 'ADI', 'TLSAE']);
            $table->enum('delivery_type', ['internet', 'in_person', 'cd_rom', 'video', 'dvd']);
            $table->integer('total_ordered')->default(0);
            $table->integer('total_used')->default(0);
            $table->integer('available_count')->default(0);
            $table->integer('provider_hold')->default(0);
            $table->integer('school_hold')->default(0);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_inventory');
    }
};
