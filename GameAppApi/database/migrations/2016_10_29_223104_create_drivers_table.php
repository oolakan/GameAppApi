<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('driver_name');
            $table->string('driver_email');
            $table->string('password');
            $table->string('driver_phone_no');
            $table->string('driver_img');
            $table->string('driver_current_lat');
            $table->string('driver_current_lng');
            $table->string('driver_current_place_id');
            $table->string('driver_current_place_name');
            $table->string('driver_current_place_vicinity');
            $table->string('driver_current_country');
            $table->timestamps();
            $table->integer('login_status')->unsigned();
            $table->integer('taxi_id');

            $table->foreign('taxi_id')
                ->references('id')
                ->on('taxis');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('drivers');
    }
}
