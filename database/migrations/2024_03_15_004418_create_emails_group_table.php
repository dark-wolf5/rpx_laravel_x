<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailsGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails_group', function (Blueprint $table) {
            $table->id()->references('group_id')->on('email');
            $table->uuid('uuid')->default(DB::raw('(UUID())'));
            $table->text('email_body');
            $table->unsignedBigInteger('from_id')->references('id')->on('business');
            $table->unsignedFloat('price', 8, 5)->nullable()->default(0);
            $table->unsignedInteger('total_sent')->nullable()->default(0);
            $table->unsignedInteger('total')->nullable()->default(0);
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
        Schema::dropIfExists('emails_group');
    }
}
