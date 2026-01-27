<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to alter the enum column
        DB::statement("ALTER TABLE payments MODIFY COLUMN gateway ENUM('stripe', 'paypal', 'authorizenet') NOT NULL");
    }

    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE payments MODIFY COLUMN gateway ENUM('stripe', 'paypal') NOT NULL");
    }
};
