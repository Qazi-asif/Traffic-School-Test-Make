<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('counties')) {
            Schema::create('counties', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('state', 2);
                $table->string('code')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('counties');
    }
};
