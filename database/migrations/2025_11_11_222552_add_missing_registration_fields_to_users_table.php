<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add fields that don't exist yet
            $table->string('license_state')->nullable();
            $table->string('license_class')->nullable();
            $table->string('court_selected')->nullable();
            $table->string('citation_number')->nullable();
            $table->integer('due_month')->nullable();
            $table->integer('due_day')->nullable();
            $table->integer('due_year')->nullable();
            $table->string('security_q1')->nullable();
            $table->string('security_q2')->nullable();
            $table->string('security_q3')->nullable();
            $table->string('security_q4')->nullable();
            $table->string('security_q5')->nullable();
            $table->string('security_q6')->nullable();
            $table->string('security_q7')->nullable();
            $table->string('security_q8')->nullable();
            $table->string('security_q9')->nullable();
            $table->string('security_q10')->nullable();
            $table->string('agreement_name')->nullable();
            $table->boolean('terms_agreement')->default(false);
            $table->timestamp('registration_completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'license_state', 'license_class',
                'court_selected', 'citation_number',
                'due_month', 'due_day', 'due_year',
                'security_q1', 'security_q2', 'security_q3', 'security_q4', 'security_q5',
                'security_q6', 'security_q7', 'security_q8', 'security_q9', 'security_q10',
                'agreement_name', 'terms_agreement', 'registration_completed_at',
            ]);
        });
    }
};
