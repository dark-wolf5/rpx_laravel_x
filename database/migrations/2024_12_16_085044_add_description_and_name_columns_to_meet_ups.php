<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionAndNameColumnsToMeetUps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_ups', function (Blueprint $table) {
            $table->string('name', 35);
            $table->string('description', 350);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meet_ups', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('name');
        });
    }
}
