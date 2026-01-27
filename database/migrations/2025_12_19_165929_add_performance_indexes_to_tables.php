<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add indexes to user_course_enrollments table (check if table exists and indexes don't exist)
        if (Schema::hasTable('user_course_enrollments')) {
            Schema::table('user_course_enrollments', function (Blueprint $table) {
                // Only add if index doesn't exist
                $indexes = DB::select("SHOW INDEX FROM user_course_enrollments WHERE Key_name = 'idx_user_course'");
                if (empty($indexes)) {
                    $table->index(['user_id', 'course_id'], 'idx_user_course');
                }
                
                $indexes = DB::select("SHOW INDEX FROM user_course_enrollments WHERE Key_name = 'idx_payment_status'");
                if (empty($indexes)) {
                    $table->index(['payment_status'], 'idx_payment_status');
                }
                
                $indexes = DB::select("SHOW INDEX FROM user_course_enrollments WHERE Key_name = 'idx_enrollment_status'");
                if (empty($indexes)) {
                    $table->index(['status'], 'idx_enrollment_status');
                }
                
                $indexes = DB::select("SHOW INDEX FROM user_course_enrollments WHERE Key_name = 'idx_enrolled_at'");
                if (empty($indexes)) {
                    $table->index(['enrolled_at'], 'idx_enrolled_at');
                }
                
                $indexes = DB::select("SHOW INDEX FROM user_course_enrollments WHERE Key_name = 'idx_course_table'");
                if (empty($indexes)) {
                    $table->index(['course_table'], 'idx_course_table');
                }
            });
        }

        // Add indexes to payments table (check if table exists and indexes don't exist)
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_payments_user'");
                if (empty($indexes)) {
                    $table->index(['user_id'], 'idx_payments_user');
                }
                
                $indexes = DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_payments_enrollment'");
                if (empty($indexes)) {
                    $table->index(['enrollment_id'], 'idx_payments_enrollment');
                }
                
                $indexes = DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_payments_status'");
                if (empty($indexes)) {
                    $table->index(['status'], 'idx_payments_status');
                }
                
                $indexes = DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_payments_created'");
                if (empty($indexes)) {
                    $table->index(['created_at'], 'idx_payments_created');
                }
                
                $indexes = DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_payments_coupon'");
                if (empty($indexes)) {
                    $table->index(['coupon_code'], 'idx_payments_coupon');
                }
            });
        }

        // Add indexes to course_chapters table (check if table exists and indexes don't exist)
        if (Schema::hasTable('course_chapters')) {
            Schema::table('course_chapters', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM course_chapters WHERE Key_name = 'idx_chapters_course'");
                if (empty($indexes)) {
                    $table->index(['course_id'], 'idx_chapters_course');
                }
                
                $indexes = DB::select("SHOW INDEX FROM course_chapters WHERE Key_name = 'idx_chapters_order'");
                if (empty($indexes)) {
                    $table->index(['order_index'], 'idx_chapters_order');
                }
                
                $indexes = DB::select("SHOW INDEX FROM course_chapters WHERE Key_name = 'idx_chapters_active'");
                if (empty($indexes)) {
                    $table->index(['is_active'], 'idx_chapters_active');
                }
            });
        }

        // Add indexes to chapter_questions table (if table exists)
        if (Schema::hasTable('chapter_questions')) {
            Schema::table('chapter_questions', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM chapter_questions WHERE Key_name = 'idx_questions_chapter'");
                if (empty($indexes)) {
                    $table->index(['chapter_id'], 'idx_questions_chapter');
                }
                
                if (Schema::hasColumn('chapter_questions', 'question_order')) {
                    $indexes = DB::select("SHOW INDEX FROM chapter_questions WHERE Key_name = 'idx_questions_order'");
                    if (empty($indexes)) {
                        $table->index(['question_order'], 'idx_questions_order');
                    }
                }
                
                if (Schema::hasColumn('chapter_questions', 'is_active')) {
                    $indexes = DB::select("SHOW INDEX FROM chapter_questions WHERE Key_name = 'idx_questions_active'");
                    if (empty($indexes)) {
                        $table->index(['is_active'], 'idx_questions_active');
                    }
                }
            });
        }

        // Add indexes to final_exam_questions table (check if table and indexes don't exist)
        if (Schema::hasTable('final_exam_questions')) {
            Schema::table('final_exam_questions', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM final_exam_questions WHERE Key_name = 'idx_final_exam_course'");
                if (empty($indexes)) {
                    $table->index(['course_id'], 'idx_final_exam_course');
                }
                
                if (Schema::hasColumn('final_exam_questions', 'question_order')) {
                    $indexes = DB::select("SHOW INDEX FROM final_exam_questions WHERE Key_name = 'idx_final_exam_order'");
                    if (empty($indexes)) {
                        $table->index(['question_order'], 'idx_final_exam_order');
                    }
                }
                
                if (Schema::hasColumn('final_exam_questions', 'is_active')) {
                    $indexes = DB::select("SHOW INDEX FROM final_exam_questions WHERE Key_name = 'idx_final_exam_active'");
                    if (empty($indexes)) {
                        $table->index(['is_active'], 'idx_final_exam_active');
                    }
                }
            });
        }

        // Add indexes to progress_table (check if table and indexes don't exist)
        if (Schema::hasTable('progress_table')) {
            Schema::table('progress_table', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM progress_table WHERE Key_name = 'idx_progress_user_course'");
                if (empty($indexes)) {
                    $table->index(['user_id', 'course_id'], 'idx_progress_user_course');
                }
                
                $indexes = DB::select("SHOW INDEX FROM progress_table WHERE Key_name = 'idx_progress_chapter'");
                if (empty($indexes)) {
                    $table->index(['chapter_id'], 'idx_progress_chapter');
                }
                
                $indexes = DB::select("SHOW INDEX FROM progress_table WHERE Key_name = 'idx_progress_completed'");
                if (empty($indexes)) {
                    $table->index(['completed'], 'idx_progress_completed');
                }
                
                $indexes = DB::select("SHOW INDEX FROM progress_table WHERE Key_name = 'idx_progress_updated'");
                if (empty($indexes)) {
                    $table->index(['updated_at'], 'idx_progress_updated');
                }
            });
        }

        // Add indexes to florida_courses table (check if table exists and indexes don't exist)
        if (Schema::hasTable('florida_courses')) {
            Schema::table('florida_courses', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM florida_courses WHERE Key_name = 'idx_florida_courses_active'");
                if (empty($indexes)) {
                    $table->index(['is_active'], 'idx_florida_courses_active');
                }
                
                $indexes = DB::select("SHOW INDEX FROM florida_courses WHERE Key_name = 'idx_florida_courses_state'");
                if (empty($indexes)) {
                    $table->index(['state_code'], 'idx_florida_courses_state');
                }
                
                $indexes = DB::select("SHOW INDEX FROM florida_courses WHERE Key_name = 'idx_florida_courses_created'");
                if (empty($indexes)) {
                    $table->index(['created_at'], 'idx_florida_courses_created');
                }
            });
        }

        // Add indexes to courses table (check if table exists and indexes don't exist)
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM courses WHERE Key_name = 'idx_courses_active'");
                if (empty($indexes)) {
                    $table->index(['is_active'], 'idx_courses_active');
                }
                
                $indexes = DB::select("SHOW INDEX FROM courses WHERE Key_name = 'idx_courses_state'");
                if (empty($indexes)) {
                    $table->index(['state_code'], 'idx_courses_state');
                }
                
                $indexes = DB::select("SHOW INDEX FROM courses WHERE Key_name = 'idx_courses_created'");
                if (empty($indexes)) {
                    $table->index(['created_at'], 'idx_courses_created');
                }
            });
        }

        // Add indexes to coupons table (check if table exists and indexes don't exist)
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM coupons WHERE Key_name = 'idx_coupons_code'");
                if (empty($indexes)) {
                    $table->index(['code'], 'idx_coupons_code');
                }
                
                $indexes = DB::select("SHOW INDEX FROM coupons WHERE Key_name = 'idx_coupons_status'");
                if (empty($indexes)) {
                    $table->index(['is_active', 'is_used'], 'idx_coupons_status');
                }
                
                $indexes = DB::select("SHOW INDEX FROM coupons WHERE Key_name = 'idx_coupons_expires'");
                if (empty($indexes)) {
                    $table->index(['expires_at'], 'idx_coupons_expires');
                }
            });
        }

        // Add indexes to state_transmissions table (check if table exists and indexes don't exist)
        if (Schema::hasTable('state_transmissions')) {
            Schema::table('state_transmissions', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEX FROM state_transmissions WHERE Key_name = 'idx_transmissions_enrollment'");
                if (empty($indexes)) {
                    $table->index(['enrollment_id'], 'idx_transmissions_enrollment');
                }
                
                $indexes = DB::select("SHOW INDEX FROM state_transmissions WHERE Key_name = 'idx_transmissions_state'");
                if (empty($indexes)) {
                    $table->index(['state'], 'idx_transmissions_state');
                }
                
                $indexes = DB::select("SHOW INDEX FROM state_transmissions WHERE Key_name = 'idx_transmissions_status'");
                if (empty($indexes)) {
                    $table->index(['status'], 'idx_transmissions_status');
                }
                
                $indexes = DB::select("SHOW INDEX FROM state_transmissions WHERE Key_name = 'idx_transmissions_sent'");
                if (empty($indexes)) {
                    $table->index(['sent_at'], 'idx_transmissions_sent');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropIndex('idx_user_course');
            $table->dropIndex('idx_payment_status');
            $table->dropIndex('idx_enrollment_status');
            $table->dropIndex('idx_enrolled_at');
            $table->dropIndex('idx_course_table');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_user');
            $table->dropIndex('idx_payments_enrollment');
            $table->dropIndex('idx_payments_status');
            $table->dropIndex('idx_payments_created');
            $table->dropIndex('idx_payments_coupon');
        });

        Schema::table('course_chapters', function (Blueprint $table) {
            $table->dropIndex('idx_chapters_course');
            $table->dropIndex('idx_chapters_order');
            $table->dropIndex('idx_chapters_active');
        });

        if (Schema::hasTable('chapter_questions')) {
            Schema::table('chapter_questions', function (Blueprint $table) {
                $table->dropIndex('idx_questions_chapter');
                if (Schema::hasColumn('chapter_questions', 'question_order')) {
                    $table->dropIndex('idx_questions_order');
                }
                if (Schema::hasColumn('chapter_questions', 'is_active')) {
                    $table->dropIndex('idx_questions_active');
                }
            });
        }

        Schema::table('final_exam_questions', function (Blueprint $table) {
            $table->dropIndex('idx_final_exam_course');
            if (Schema::hasColumn('final_exam_questions', 'question_order')) {
                $table->dropIndex('idx_final_exam_order');
            }
            if (Schema::hasColumn('final_exam_questions', 'is_active')) {
                $table->dropIndex('idx_final_exam_active');
            }
        });

        Schema::table('progress_table', function (Blueprint $table) {
            $table->dropIndex('idx_progress_user_course');
            $table->dropIndex('idx_progress_chapter');
            $table->dropIndex('idx_progress_completed');
            $table->dropIndex('idx_progress_updated');
        });

        Schema::table('florida_courses', function (Blueprint $table) {
            $table->dropIndex('idx_florida_courses_active');
            $table->dropIndex('idx_florida_courses_state');
            $table->dropIndex('idx_florida_courses_created');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_courses_active');
            $table->dropIndex('idx_courses_state');
            $table->dropIndex('idx_courses_created');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex('idx_coupons_code');
            $table->dropIndex('idx_coupons_status');
            $table->dropIndex('idx_coupons_expires');
        });

        Schema::table('state_transmissions', function (Blueprint $table) {
            $table->dropIndex('idx_transmissions_enrollment');
            $table->dropIndex('idx_transmissions_state');
            $table->dropIndex('idx_transmissions_status');
            $table->dropIndex('idx_transmissions_sent');
        });
    }
};