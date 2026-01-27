<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('driver_license')->nullable()->after('address');
            $table->dropColumn('name'); // Remove default name field
        });

        // Update status column separately for MySQL compatibility
        DB::statement("UPDATE users SET status = 'active' WHERE status IS NULL");
        DB::statement("ALTER TABLE users MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'driver_license']);
            $table->string('name')->after('id');
        });

        DB::statement('ALTER TABLE users MODIFY COLUMN status VARCHAR(255) NULL');
    }
};
