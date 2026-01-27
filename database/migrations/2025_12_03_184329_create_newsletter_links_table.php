<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('newsletter_campaigns')->onDelete('cascade');
            $table->text('original_url');
            $table->string('tracking_code', 32)->unique();
            $table->integer('click_count')->default(0);
            $table->timestamps();

            $table->index(['campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_links');
    }
};
