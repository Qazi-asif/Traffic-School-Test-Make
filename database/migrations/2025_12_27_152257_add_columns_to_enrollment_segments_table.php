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
        Schema::table('enrollment_segments', function (Blueprint $table) {
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('filters');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_system')->default(false);
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_segments', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['name', 'description', 'filters', 'created_by', 'is_system']);
        });
    }
};
