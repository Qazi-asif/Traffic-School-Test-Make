<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dicds_users', function (Blueprint $table) {
            $table->id();
            $table->string('user_last_name');
            $table->string('first_name');
            $table->string('middle')->nullable();
            $table->string('suffix')->nullable();
            $table->string('contact_email');
            $table->string('retype_email');
            $table->string('phone_number');
            $table->string('phone_extension')->nullable();
            $table->string('login_id')->unique();
            $table->string('password');
            $table->string('desired_application')->nullable();
            $table->enum('desired_role', ['DRS_Provider_Admin', 'DRS_Provider_User', 'DRS_School_Admin'])->nullable();
            $table->string('user_group')->nullable();
            $table->enum('status', ['Pending', 'Active', 'Denied', 'Revoked'])->default('Pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->enum('course_type', ['BDI', 'ADI', 'TLSAE']);
                $table->enum('delivery_type', ['In Person', 'Internet', 'CD-Rom', 'Video', 'DVD']);
                $table->string('description');
                $table->timestamps();
            });
        }

        Schema::create('dicds_schools', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('phone');
            $table->string('fax')->nullable();
            $table->string('email');
            $table->string('contact_person')->nullable();
            $table->foreignId('provider_id')->nullable()->constrained('dicds_users');
            $table->boolean('disable_certificates')->default(false);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('dicds_school_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('dicds_schools');
            $table->foreignId('course_id')->constrained('courses');
            $table->string('status')->default('Active');
            $table->date('status_date');
            $table->timestamps();
        });

        Schema::create('dicds_instructors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->foreignId('school_id')->constrained('dicds_schools');
            $table->timestamps();
        });

        Schema::create('dicds_instructor_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('dicds_instructors');
            $table->foreignId('course_id')->constrained('courses');
            $table->string('status')->default('Active');
            $table->date('status_date');
            $table->timestamps();
        });

        Schema::create('dicds_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('school_id')->nullable()->constrained('dicds_schools');
            $table->foreignId('provider_id')->constrained('dicds_users');
            $table->integer('certificate_count');
            $table->enum('status', ['Pending', 'Active', 'Issued'])->default('Pending');
            $table->decimal('total_amount', 10, 2);
            $table->string('student_name')->nullable();
            $table->string('certificate_number')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dicds_certificates');
        Schema::dropIfExists('dicds_instructor_courses');
        Schema::dropIfExists('dicds_instructors');
        Schema::dropIfExists('dicds_school_courses');
        Schema::dropIfExists('dicds_schools');
        Schema::dropIfExists('dicds_users');
    }
};
