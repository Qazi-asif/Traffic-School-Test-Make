<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('usage_limit');
            $table->dropColumn('used_count');
            $table->boolean('is_used')->default(false)->after('is_active');
            $table->string('code', 6)->change();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->dropColumn('is_used');
        });
    }
};
