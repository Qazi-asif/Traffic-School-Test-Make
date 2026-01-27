<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('document_type', ['privacy_policy', 'terms_of_service', 'copyright_notice', 'disclaimer', 'refund_policy']);
            $table->string('title');
            $table->text('content');
            $table->string('version');
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_consent')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
