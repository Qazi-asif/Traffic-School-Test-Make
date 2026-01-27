<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('email_course_updates')->default(true);
            $table->boolean('email_payment_receipts')->default(true);
            $table->boolean('email_certificate_alerts')->default(true);
            $table->boolean('email_dicds_status')->default(true);
            $table->boolean('email_compliance_alerts')->default(true);
            $table->boolean('sms_reminders')->default(false);
            $table->boolean('in_app_notifications')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_notification_settings');
    }
};
