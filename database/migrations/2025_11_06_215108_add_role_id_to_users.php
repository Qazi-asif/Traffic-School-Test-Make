<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add the column and foreign key if they don't exist
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('roles')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key and column only if they exist
            if (Schema::hasColumn('users', 'role_id')) {
                // Safely drop the foreign key if it exists
                $foreignKeys = $this->listTableForeignKeys('users');
                if (in_array('users_role_id_foreign', $foreignKeys)) {
                    $table->dropForeign(['role_id']);
                }
                $table->dropColumn('role_id');
            }
        });
    }

    /**
     * Helper function to get foreign keys for a table (PostgreSQL & MySQL safe)
     */
    private function listTableForeignKeys(string $tableName): array
    {
        $connection = Schema::getConnection()->getDoctrineSchemaManager();
        $doctrineTable = $connection->listTableDetails($tableName);

        return array_keys($doctrineTable->getForeignKeys());
    }
};
