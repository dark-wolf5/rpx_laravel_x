<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business', function (Blueprint $table) {
            $table->id()->unsignedBigInteger('id')->references('id')->on('users')->onDelete('cascade')->id()->unique();
            $table->string('name', 25)->nullable(false);
            $table->string('slug', 200)->nullable(false);
            $table->string('description', 500)->nullable(false);

            $table->string('address', 100)->nullable(false);

            $table->string('city', 255)->nullable(false)->default(config('rpx.my_city'));
            $table->string('country', 255)->nullable(false)->default(config('rpx.my_country'));
            $table->string('line1', 255)->nullable(false)->default(config('rpx.my_line_1'));
            $table->string('line2', 255)->nullable();
            $table->string('postal_code', 255)->nullable(false)->default(config('rpx.my_zip_code'));
            $table->string('state', 255)->nullable(false)->default(config('rpx.my_state'));

            $table->json('categories')->nullable(true);
            $table->float('loc_x', 8, 6)->nullable();
            $table->float('loc_y', 8, 6)->nullable();
            $table->string('photo', 650)->nullable(false)->default('');
            $table->boolean('is_verified')->nullable(false)->default(false);
            $table->string('qr_code_link', 135)->nullable(false);
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
        Schema::dropIfExists('business');
    }
}
