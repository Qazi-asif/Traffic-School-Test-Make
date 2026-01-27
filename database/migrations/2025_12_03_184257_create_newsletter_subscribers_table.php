<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('source', ['registration', 'checkout', 'website_form', 'import', 'manual'])->default('website_form');
            $table->string('state_code', 2)->nullable();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->string('confirmation_token', 64)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('unsubscribe_token', 64)->unique();
            $table->integer('bounce_count')->default(0);
            $table->timestamp('last_email_sent_at')->nullable();
            $table->timestamp('last_email_opened_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'is_active']);
            $table->index(['state_code', 'is_active']);
            $table->index(['source', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
