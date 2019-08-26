<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('short_name')->unique();
            $table->string('full_name');
            $table->boolean('is_verified')->default(false);
            $table->string('biography')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedBigInteger('fan_count')->default(0);
            $table->unsignedBigInteger('heart_count')->default(0);
            $table->unsignedBigInteger('following_count')->default(0);
            $table->unsignedBigInteger('video_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
