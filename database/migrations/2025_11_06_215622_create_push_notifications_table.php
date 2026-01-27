<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('push_notifications')) {
            Schema::create('push_notifications', function (Blueprint $table) {
                $table->id();

                $table->string('user_email')->nullable();
                $table->string('type')->nullable()->default('info');
                $table->string('title')->nullable();
                $table->text('message')->nullable();
                $table->boolean('is_read')->nullable()->default(false);

                $table->timestamps();

                // Composite index for performance
                $table->index(['user_email', 'is_read']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
