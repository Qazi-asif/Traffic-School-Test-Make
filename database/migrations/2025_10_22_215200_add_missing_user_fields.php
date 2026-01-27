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
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'driver_license')) {
                $table->string('driver_license')->nullable()->after('address');
            }
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name'); // Remove default name field
            }
        });

        // Ensure all existing NULL statuses are updated to 'active'
        DB::statement("UPDATE users SET status = 'active' WHERE status IS NULL");

        // PostgreSQL-compatible way to alter column type and default
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'active'");
            DB::statement('ALTER TABLE users ALTER COLUMN status SET NOT NULL');
        } else {
            // For MySQL
            DB::statement("ALTER TABLE users MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('users', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('users', 'driver_license')) {
                $table->dropColumn('driver_license');
            }
            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->after('id');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN status DROP NOT NULL');
            DB::statement('ALTER TABLE users ALTER COLUMN status DROP DEFAULT');
        } else {
            DB::statement('ALTER TABLE users MODIFY COLUMN status VARCHAR(255) NULL');
        }
    }
};
