<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('tvcc_enabled')->default(false)->after('is_active')->comment('California TVCC enabled');
            $table->boolean('ctsi_enabled')->default(false)->after('tvcc_enabled')->comment('California CTSI enabled');
            $table->boolean('ntsa_enabled')->default(false)->after('ctsi_enabled')->comment('Nevada NTSA enabled');
            $table->boolean('ccs_enabled')->default(false)->after('ntsa_enabled')->comment('CCS enabled');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['tvcc_enabled', 'ctsi_enabled', 'ntsa_enabled', 'ccs_enabled']);
        });
    }
};
