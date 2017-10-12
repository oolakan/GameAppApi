<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customer_social_id');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone_no');
            $table->string('customer_img');
            $table->string('customer_lat');
            $table->string('customer_lng');
            $table->string('customer_address');
            $table->string('customer_state');
            $table->string('customer_country');
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
        Schema::drop('customers');
    }
}
