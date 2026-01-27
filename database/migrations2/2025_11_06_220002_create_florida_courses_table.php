<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('florida_courses', function (Blueprint $table) {
            $table->id();
            $table->string('state_code')->default('FL');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_hours')->default(4);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('florida_courses');
    }
};
