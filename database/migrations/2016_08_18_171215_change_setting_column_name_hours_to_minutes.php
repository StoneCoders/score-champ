<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSettingColumnNameHoursToMinutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

	    if (Schema::hasColumn('settings', 'prevent_bet_hours_before_game')) {
		    Schema::table('settings', function (Blueprint $table) {

			    $table->renameColumn('prevent_bet_hours_before_game', 'prevent_bet_minutes_before_game');
		    });
	    }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('prevent_bet_minutes_before_game', 'prevent_bet_hours_before_game');
        });
    }
}
