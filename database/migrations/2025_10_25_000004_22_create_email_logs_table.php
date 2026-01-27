<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Prevents duplicate table creation
        if (! Schema::hasTable('email_logs')) {
            Schema::create('email_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')
                    ->nullable()
                    ->constrained('email_templates')
                    ->onDelete('set null');
                $table->string('recipient_email');
                $table->string('recipient_name')->nullable();
                $table->string('subject');
                $table->text('content');
                $table->json('variables_used')->nullable();
                $table->enum('status', ['sent', 'delivered', 'opened', 'failed', 'bounced'])->default('sent');
                $table->string('gateway_message_id')->nullable();
                $table->json('gateway_response')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('sent_at')->nullable();

                // ✅ Use Laravel's built-in timestamp management
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
