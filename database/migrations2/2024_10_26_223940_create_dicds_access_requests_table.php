<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dicds_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('desired_application')->default('Driver School Certificates');
            $table->enum('desired_role', ['DRS_Provider_Admin', 'DRS_Provider_User', 'DRS_School_Admin']);
            $table->string('user_group');
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dicds_access_requests');
    }
};
