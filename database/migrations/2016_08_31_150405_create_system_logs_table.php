<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message',800);
            $table->dateTime('when');
            $table->string('category',25);
            $table->integer('actor')->nullable();
            $table->string('controller',250)->nullable();
            $table->string('method',250)->nullable();
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
        Schema::drop('system_logs');
    }
}
