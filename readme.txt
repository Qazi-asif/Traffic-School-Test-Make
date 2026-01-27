C:\Users\lenovo\Downloads\course.dummiestrafficschool.com (2)\course.dummiestrafficschool.com>php artisan migrate --path=database/migrations/2025_12_31_000003_create_final_exam_results_table.php

   INFO  Running migrations.

  2025_12_31_000003_create_final_exam_results_table ..................................................... 54.64ms FAIL

   Illuminate\Database\QueryException

  SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'final_exam_results' already exists (Connection: mysql, SQL: create table `final_exam_results` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `enrollment_id` bigint unsigned not null, `course_id` bigint unsigned not null, `course_type` varchar(255) not null default 'courses', `final_exam_score` decimal(5, 2) not null default '0', `final_exam_correct` int not null default '0', `final_exam_total` int not null default '0', `exam_completed_at` timestamp not null, `exam_duration_minutes` int not null default '0', `quiz_average` decimal(5, 2) not null default '0', `free_response_score` decimal(5, 2) null, `overall_score` decimal(5, 2) not null default '0', `grade_letter` varchar(2) null, `status` enum('pending', 'passed', 'failed', 'under_review') not null default 'pending', `is_passing` tinyint(1) not null default '0', `passing_threshold` decimal(5, 2) not null default '80', `student_feedback` text null, `student_rating` int null, `student_feedback_at` timestamp null, `grading_period_ends_at` timestamp not null, `grading_completed` tinyint(1) not null default '0', `instructor_notes` text null, `graded_by` bigint unsigned null, `graded_at` timestamp null, `certificate_generated` tinyint(1) not null default '0', `certificate_number` varchar(255) null, `certificate_issued_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci')

  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821▕                     $this->getName(), $query, $this->prepareBindings($bindings), $e
    822▕                 );
    823▕             }
    824▕
  ➜ 825▕             throw new QueryException(
    826▕                 $this->getName(), $query, $this->prepareBindings($bindings), $e
    827▕             );
    828▕         }
    829▕     }

  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      PDOException::("SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'final_exam_results' already exists")

  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      PDOStatement::execute()


C:\Users\lenovo\Downloads\course.dummiestrafficschool.com (2)\course.dummiestrafficschool.com>php test-final-exam-fixes.php
=== Final Exam Completion Fix Verification ===

Test 1: Checking UserCourseEnrollment fillable fields...
✅ PASS: final_exam_completed and final_exam_result_id are fillable

Test 2: Checking database schema...
✅ PASS: Database columns exist
   - final_exam_completed: tinyint(1)
   - final_exam_result_id: bigint(20) unsigned

Test 3: Checking enrollments with passed final exams...
❌ FAIL: Query error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'final_exam_results.final_exam_score' in 'field list' (Connection: mysql, SQL: select `user_course_enrollments`.`id`, `user_course_enrollments`.`status`, `user_course_enrollments`.`progress_percentage`, `user_course_enrollments`.`final_exam_completed`, `final_exam_results`.`final_exam_score`, `final_exam_results`.`is_passing` from `final_exam_results` inner join `user_course_enrollments` on `final_exam_results`.`enrollment_id` = `user_course_enrollments`.`id` where `final_exam_results`.`is_passing` = 1 limit 5)

Test 4: Checking for duplicate passed results...
❌ FAIL: Query error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'is_passing' in 'where clause' (Connection: mysql, SQL: select `enrollment_id`, COUNT(*) as count from `final_exam_results` where `is_passing` = 1 group by `enrollment_id` having `count` > 1)

Test 5: Checking if process-completion route exists...
✅ PASS: Route found
   URI: final-exam/process-completion
   Method: POST
   Action: App\Http\Controllers\FinalExamResultController@processExamCompletion

=== Verification Complete ===

C:\Users\lenovo\Downloads\course.dummiestrafficschool.com (2)\course.dummiestrafficschool.com>






































































