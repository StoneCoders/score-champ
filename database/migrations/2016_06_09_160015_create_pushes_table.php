<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pushes', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('already_started')->default(false);
            $table->boolean('finished')->default(false);
            $table->string('type');
            $table->string('title_he');
            $table->string('msg_he');
            $table->string('title');
            $table->string('msg');
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
        Schema::drop('pushes');
    }
}
