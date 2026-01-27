<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('state_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('state_code', 2)->unique();
            $table->string('state_name');
            $table->json('compliance_rules')->nullable();
            $table->enum('submission_method', ['api', 'portal', 'email', 'manual']);
            $table->string('api_endpoint')->nullable();
            $table->text('api_credentials')->nullable(); // encrypted
            $table->string('portal_url')->nullable();
            $table->text('portal_credentials')->nullable(); // encrypted
            $table->string('email_recipient')->nullable();
            $table->string('certificate_template');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('state_configurations');
    }
};
