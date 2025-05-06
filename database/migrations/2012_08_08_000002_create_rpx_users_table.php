<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRpxUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpx_users', function (Blueprint $table) {
            $table->id()->unsignedBigInteger('id')->references('id')->on('users')->onDelete('cascade')->id()->unique();
            $table->smallInteger('user_type')->default('0');
            $table->string('default_picture', 100)->default('assets/images/guest-rpx-user-01.svg');
            $table->string('first_name', 72)->nullabe();
            $table->string('last_name', 72)->nullabe();
            $table->timestamp('last_log_in')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('description', 500)->nullable();
            $table->string('last_known_ip_address', 256)->nullable();
            $table->string('phone_number', 35)->nullable()->default(null);
            $table->boolean('confirmed')->default(false);
            $table->unsignedInteger('confirm_attempts')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpx_users');
    }
}
