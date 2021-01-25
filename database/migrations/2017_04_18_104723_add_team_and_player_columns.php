<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamAndPlayerColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('match_weeks', function (Blueprint $table) {
            $table->integer('league_id');
        });
        
        Schema::table('top_score_players', function (Blueprint $table) {
            $table->integer('league_id');
        });
        
        Schema::table('winning_teams', function (Blueprint $table) {
            $table->integer('league_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('match_weeks', function (Blueprint $table) {
            $table->dropColumn('league_id');
        });

        Schema::table('top_score_players', function (Blueprint $table) {
            $table->dropColumn('league_id');
        });

        Schema::table('winning_teams', function (Blueprint $table) {
            $table->dropColumn('league_id');
        });
    }
}
