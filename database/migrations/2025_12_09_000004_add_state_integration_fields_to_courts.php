<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->string('tvcc_court_code', 50)->nullable()->after('state')->comment('California TVCC court code');
            $table->string('ctsi_court_id', 50)->nullable()->after('tvcc_court_code')->comment('California CTSI court ID');
            $table->string('ntsa_court_name', 255)->nullable()->after('ctsi_court_id')->comment('Nevada NTSA court name');
        });
    }

    public function down(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->dropColumn(['tvcc_court_code', 'ctsi_court_id', 'ntsa_court_name']);
        });
    }
};
