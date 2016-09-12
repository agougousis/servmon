<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebappsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webapps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url',100)->unique();
            $table->integer('server');
            $table->string('language',15);
            $table->string('developer',50);
            $table->string('contact',50);
            $table->integer('owner')->unsigned();
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
        Schema::drop('webapps');
    }
}
