<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create support_tickets table
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('subject');
                $table->text('description');
                $table->string('email');
                $table->enum('status', ['open', 'replied', 'resolved', 'closed'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->string('category')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('status');
                $table->index('created_at');
            });
        }

        // Create support_ticket_replies table
        if (!Schema::hasTable('support_ticket_replies')) {
            Schema::create('support_ticket_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('support_ticket_id')->constrained('support_tickets')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('message');
                $table->boolean('is_staff_reply')->default(false);
                $table->timestamps();
                
                $table->index('support_ticket_id');
                $table->index('user_id');
                $table->index('created_at');
            });
        }

        // Create ticket_recipients table
        if (!Schema::hasTable('ticket_recipients')) {
            Schema::create('ticket_recipients', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->unique('email');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_recipients');
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
    }
};
