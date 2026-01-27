<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Prevents "table already exists" error
        if (! Schema::hasTable('compliance_rules')) {
            Schema::create('compliance_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_config_id')
                    ->constrained('state_configurations')
                    ->onDelete('cascade');
                $table->enum('rule_type', ['timing', 'grading', 'content', 'submission']);
                $table->string('rule_name');
                $table->string('rule_value');
                $table->text('description')->nullable();
                $table->boolean('is_required')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_rules');
    }
};
