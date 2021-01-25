<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomSettingsToLeagues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leagues', function (Blueprint $table) {
          $table->longText("html_rules_en");
          $table->longText("html_rules_he");
          $table->string("global_rank_title_en");
	        $table->string("global_rank_title_he");
	        $table->string("week_rank_title_en");
	        $table->string("week_rank_title_he");
	        $table->tinyInteger("show_league_board")->default(1);
	        $table->tinyInteger("show_global_rank")->default(1);
	        $table->tinyInteger("show_week_rank")->default(1);
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
        	$table->dropColumn("html_rules_en");
	        $table->dropColumn("html_rules_he");
	        $table->dropColumn("global_rank_title_en");
	        $table->dropColumn("global_rank_title_he");
	        $table->dropColumn("week_rank_title_en");
	        $table->dropColumn("week_rank_title_he");
	        $table->dropColumn("show_league_board");
	        $table->dropColumn("show_global_rank");
	        $table->dropColumn("show_week_rank");
        });
    }
}
