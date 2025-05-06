<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMeetUpIdColumnInMeetUpInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_up_invitations', function (Blueprint $table) {
            $table->unsignedBigInteger('meet_up_id')
                ->references('id')
                ->on('meets_up')
                ->onDelete('cascade')
                ->change();
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
            $table->unsignedBigInteger('meet_up_id')
                ->references('id')
                ->on('meets_up')->change();
        });
    }
}
