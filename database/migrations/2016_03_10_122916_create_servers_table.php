<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip',35)->unique();
            $table->string('os',50);
            $table->string('hostname',100);
            $table->integer('owner')->unsigned();
            $table->integer('domain')->unsigned();
            $table->tinyInteger('watch')->unsigned()->default(0);
            $table->string('supervisor_email',70);
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
        Schema::drop('servers');
    }
}
