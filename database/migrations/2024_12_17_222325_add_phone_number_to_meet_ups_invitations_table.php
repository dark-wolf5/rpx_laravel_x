<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneNumberToMeetUpsInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_up_invitations', function (Blueprint $table) {
            $table->text('friend_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meet_up_invitations', function (Blueprint $table) {
            $table->unsignedBigInteger('friend_id')->references('id')->on('users')->change();
        });
    }
}
