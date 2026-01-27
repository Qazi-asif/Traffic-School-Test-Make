<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('event_type', ['login', 'logout', 'failed_login', 'password_change', 'data_access', 'system_change', 'dicds_submission', 'certificate_generation', 'payment_processed', 'admin_action']);
            $table->string('ip_address');
            $table->text('user_agent');
            $table->json('location_data')->nullable();
            $table->text('description');
            $table->json('florida_metadata')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('florida_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('model_type');
            $table->bigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->boolean('florida_required')->default(false);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url');
            $table->string('method');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('florida_compliance_checks', function (Blueprint $table) {
            $table->id();
            $table->enum('check_type', ['daily', 'weekly', 'monthly', 'quarterly', 'annual']);
            $table->string('check_name');
            $table->enum('status', ['passed', 'failed', 'warning']);
            $table->json('details');
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('performed_at');
            $table->date('next_due_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_compliance_checks');
        Schema::dropIfExists('florida_audit_trails');
        Schema::dropIfExists('florida_security_logs');
    }
};
