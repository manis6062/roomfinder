<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spam', function (Blueprint $table) {
       $table->increments('id');
        $table->integer('user_id')->unsigned();
        $table->text('complains');
         $table->foreign('user_id')->references('id')->on('users');
         $table->integer('room_id')->unsigned()->nullable();
         $table->foreign('room_id')->references('id')->on('rooms');
         $table->integer('jagga_id')->unsigned()->nullable();
         $table->foreign('jagga_id')->references('id')->on('jaggas');
        $table->enum('read', ['0', '1'])->default('0');
                    $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('spam');
    }
}
