<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_schools', function (Blueprint $table) {
            $table->id();
            $table->string('school_id')->unique();
            $table->string('school_name');
            $table->text('address');
            $table->string('phone');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_schools');
    }
};
