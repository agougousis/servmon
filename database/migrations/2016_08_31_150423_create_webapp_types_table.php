<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebappTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webapp_types', function (Blueprint $table) {
            $table->string('codename',25);
            $table->string('title',150);
            $table->string('image',250);
            
            $table->primary('codename');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('webapp_types');
    }
}
