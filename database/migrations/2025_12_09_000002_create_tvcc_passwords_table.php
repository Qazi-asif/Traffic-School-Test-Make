<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tvcc_passwords', function (Blueprint $table) {
            $table->id();
            $table->text('password')->comment('Encrypted California TVCC password');
            $table->timestamp('updated_at');
        });

        // Insert default password (store plain text, encrypt in service)
        DB::table('tvcc_passwords')->insert([
            'password' => 'change_me_in_production',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tvcc_passwords');
    }
};
