<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_click_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained('newsletter_links')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('newsletter_campaign_recipients')->onDelete('cascade');
            $table->timestamp('clicked_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->index(['link_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_click_logs');
    }
};
