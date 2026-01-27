<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mailing_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->date('batch_date');
            $table->integer('total_items')->default(0);
            $table->integer('printed_count')->default(0);
            $table->integer('mailed_count')->default(0);
            $table->decimal('total_postage', 10, 2)->nullable();
            $table->enum('status', ['open', 'printing', 'ready_to_mail', 'mailed', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailing_batches');
    }
};
