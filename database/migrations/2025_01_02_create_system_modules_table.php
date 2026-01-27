<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_name')->unique();
            $table->boolean('enabled')->default(true);
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at');
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default module states
        DB::table('system_modules')->insert([
            ['module_name' => 'user_registration', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'course_enrollment', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'payment_processing', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'certificate_generation', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'state_transmissions', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'admin_panel', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'announcements', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'course_content', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'student_feedback', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'final_exams', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'reports', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'email_system', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'support_tickets', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['module_name' => 'booklet_orders', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('system_modules');
        Schema::dropIfExists('system_settings');
    }
};