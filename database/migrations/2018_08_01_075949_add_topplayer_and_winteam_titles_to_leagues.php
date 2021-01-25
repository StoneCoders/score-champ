<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTopplayerAndWinteamTitlesToLeagues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leagues', function (Blueprint $table) {

	        $table->string("winning_team_title_en");
	        $table->string("winning_team_title_he");
	        $table->string("top_player_title_en");
	        $table->string("top_player_title_he");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leagues', function (Blueprint $table) {
	        $table->dropColumn("winning_team_title_en");
	        $table->dropColumn("winning_team_title_he");
	        $table->dropColumn("top_player_title_en");
	        $table->dropColumn("top_player_title_he");
        });
    }
}
