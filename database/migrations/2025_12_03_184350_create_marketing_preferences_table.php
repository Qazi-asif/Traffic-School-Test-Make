<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->boolean('receive_newsletters')->default(true);
            $table->boolean('receive_promotions')->default(true);
            $table->boolean('receive_course_updates')->default(true);
            $table->boolean('receive_partner_offers')->default(false);
            $table->enum('preferred_frequency', ['immediate', 'daily', 'weekly', 'monthly'])->default('immediate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_preferences');
    }
};
