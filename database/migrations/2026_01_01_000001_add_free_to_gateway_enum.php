<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Change enum to include 'free'
            $table->enum('gateway', ['stripe', 'paypal', 'free'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Revert to original enum
            $table->enum('gateway', ['stripe', 'paypal'])->change();
        });
    }
};
