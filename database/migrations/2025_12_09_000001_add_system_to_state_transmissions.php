<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('state_transmissions', function (Blueprint $table) {
            $table->string('system', 20)->after('state')->nullable()->comment('TVCC, CTSI, NTSA, CCS, FLHSMV');
            $table->index(['state', 'system']);
        });
    }

    public function down(): void
    {
        Schema::table('state_transmissions', function (Blueprint $table) {
            $table->dropIndex(['state', 'system']);
            $table->dropColumn('system');
        });
    }
};
