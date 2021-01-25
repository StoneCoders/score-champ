<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLeagueRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_league_ranks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('league_id');
            $table->integer('global_rank')->default(0);
            $table->integer('global_points')->default(0);
            $table->integer('global_hits')->default(0);
            $table->integer('global_exact_hits')->default(0);
            $table->integer('week_points')->default(0);
            $table->integer('week_rank')->default(0);
            $table->integer('week_hits')->default(0);
            $table->integer('week_exact_hits')->default(0);
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
        Schema::drop('user_league_ranks');
    }
}
