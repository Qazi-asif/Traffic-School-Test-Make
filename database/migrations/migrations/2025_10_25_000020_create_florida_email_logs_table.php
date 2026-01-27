<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('florida_email_templates')->onDelete('set null');
            $table->foreignId('enrollment_id')->nullable()->constrained('user_course_enrollments')->onDelete('set null');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->text('content');
            $table->json('florida_variables_used')->nullable();
            $table->string('dicds_reference')->nullable();
            $table->enum('status', ['sent', 'delivered', 'opened', 'failed', 'bounced']);
            $table->string('gateway_message_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_email_logs');
    }
};
