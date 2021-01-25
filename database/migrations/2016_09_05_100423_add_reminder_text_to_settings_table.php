<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReminderTextToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('reminder_content_he');
            $table->string('reminder_content_en');
            $table->string('reminder_title_he');
            $table->string('reminder_title_en');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('reminder_content_he');
            $table->dropColumn('reminder_content_en');
            $table->dropColumn('reminder_title_he');
            $table->dropColumn('reminder_title_en');
        });
    }
}
