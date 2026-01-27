<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable();
            }
            if (! Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable();
            }
            if (! Schema::hasColumn('users', 'zip')) {
                $table->string('zip')->nullable();
            }
            if (! Schema::hasColumn('users', 'phone_1')) {
                $table->string('phone_1')->nullable();
            }
            if (! Schema::hasColumn('users', 'phone_2')) {
                $table->string('phone_2')->nullable();
            }
            if (! Schema::hasColumn('users', 'phone_3')) {
                $table->string('phone_3')->nullable();
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable();
            }
            if (! Schema::hasColumn('users', 'birth_month')) {
                $table->integer('birth_month')->nullable();
            }
            if (! Schema::hasColumn('users', 'birth_day')) {
                $table->integer('birth_day')->nullable();
            }
            if (! Schema::hasColumn('users', 'birth_year')) {
                $table->integer('birth_year')->nullable();
            }
            if (! Schema::hasColumn('users', 'driver_license')) {
                $table->string('driver_license')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['city', 'state', 'zip', 'phone_1', 'phone_2', 'phone_3', 'gender', 'birth_month', 'birth_day', 'birth_year', 'driver_license'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
