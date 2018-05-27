<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('type' , 200);
            $table->integer('no_of_floor');
            $table->integer('no_of_room');
            $table->string('parking');
            $table->string('kitchen');
            $table->string('restroom');
            $table->string('phone_no');
            $table->double('loc_lat');
            $table->double('loc_lon');
            $table->string('address');
             $table->string('preference');
            $table->double('price');
             $table->text('description');
             $table->boolean('occupied');
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
        Schema::dropIfExists('rooms');
    }
}
