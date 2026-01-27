<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('timer_sessions', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('timer_sessions', 'browser_fingerprint')) {
                $table->text('browser_fingerprint')->nullable()->after('session_token');
            }
            if (!Schema::hasColumn('timer_sessions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('browser_fingerprint');
            }
            if (!Schema::hasColumn('timer_sessions', 'tab_switches')) {
                $table->integer('tab_switches')->default(0)->after('ip_address');
            }
            if (!Schema::hasColumn('timer_sessions', 'page_reloads')) {
                $table->integer('page_reloads')->default(0)->after('tab_switches');
            }
            if (!Schema::hasColumn('timer_sessions', 'focus_losses')) {
                $table->integer('focus_losses')->default(0)->after('page_reloads');
            }
            if (!Schema::hasColumn('timer_sessions', 'resume_count')) {
                $table->integer('resume_count')->default(0)->after('focus_losses');
            }
            if (!Schema::hasColumn('timer_sessions', 'resumed_at')) {
                $table->timestamp('resumed_at')->nullable()->after('resume_count');
            }
            if (!Schema::hasColumn('timer_sessions', 'last_heartbeat')) {
                $table->timestamp('last_heartbeat')->nullable()->after('resumed_at');
            }
            if (!Schema::hasColumn('timer_sessions', 'bypassed_by_user_id')) {
                $table->integer('bypassed_by_user_id')->nullable()->after('last_heartbeat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timer_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'browser_fingerprint',
                'ip_address',
                'tab_switches',
                'page_reloads',
                'focus_losses',
                'resume_count',
                'resumed_at',
                'last_heartbeat',
                'bypassed_by_user_id'
            ]);
        });
    }
};