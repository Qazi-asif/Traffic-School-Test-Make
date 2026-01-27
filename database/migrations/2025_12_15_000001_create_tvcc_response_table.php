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
        Schema::create('tvcc_response', function (Blueprint $table) {
            $table->id();
            $table->string('vscid', 50)->nullable();
            $table->string('certificate_number', 100)->nullable();
            $table->text('response_data')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('vscid');
            $table->index('certificate_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tvcc_response');
    }
};