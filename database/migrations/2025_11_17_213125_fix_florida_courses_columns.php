<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('florida_courses', function (Blueprint $table) {
            // Rename state to state_code if exists
            if (Schema::hasColumn('florida_courses', 'state') && ! Schema::hasColumn('florida_courses', 'state_code')) {
                $table->renameColumn('state', 'state_code');
            }

            // Add missing columns if they don't exist
            // if (!Schema::hasColumn('florida_courses', 'state_code')) {
            //     $table->string('state_code', 50)->nullable()->after('description');
            // }

            if (! Schema::hasColumn('florida_courses', 'min_pass_score')) {
                $table->integer('min_pass_score')->default(80)->after('state_code');
            } elseif (Schema::hasColumn('florida_courses', 'passing_score')) {
                $table->renameColumn('passing_score', 'min_pass_score');
            }

            if (! Schema::hasColumn('florida_courses', 'total_duration')) {
                $table->integer('total_duration')->default(0)->after('min_pass_score');
            } elseif (Schema::hasColumn('florida_courses', 'duration')) {
                $table->renameColumn('duration', 'total_duration');
            }

            if (! Schema::hasColumn('florida_courses', 'dicds_course_id')) {
                $table->string('dicds_course_id')->nullable()->after('price');
            }

            if (! Schema::hasColumn('florida_courses', 'certificate_template')) {
                $table->string('certificate_template')->nullable()->after('dicds_course_id');
            } elseif (Schema::hasColumn('florida_courses', 'certificate_type')) {
                $table->renameColumn('certificate_type', 'certificate_template');
            }

            if (! Schema::hasColumn('florida_courses', 'delivery_type')) {
                $table->string('delivery_type')->nullable()->after('course_type');
            }

            if (! Schema::hasColumn('florida_courses', 'copyright_protected')) {
                $table->boolean('copyright_protected')->default(false)->after('certificate_template');
            }
        });
    }

    public function down(): void
    {
        Schema::table('florida_courses', function (Blueprint $table) {
            if (Schema::hasColumn('florida_courses', 'state_code')) {
                $table->renameColumn('state_code', 'state');
            }
            if (Schema::hasColumn('florida_courses', 'min_pass_score')) {
                $table->renameColumn('min_pass_score', 'passing_score');
            }
            if (Schema::hasColumn('florida_courses', 'total_duration')) {
                $table->renameColumn('total_duration', 'duration');
            }
            if (Schema::hasColumn('florida_courses', 'certificate_template')) {
                $table->renameColumn('certificate_template', 'certificate_type');
            }
            $table->dropColumn(['dicds_course_id', 'delivery_type', 'copyright_protected']);
        });
    }
};
