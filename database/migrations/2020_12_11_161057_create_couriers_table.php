<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouriersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('awb_number')->nullable();
            $table->integer('adding_user_id');
            $table->integer('sender_id');
            $table->integer('receiver_id');
            $table->integer('courier_type');
            $table->string('item');
            $table->integer('weight')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('pincode',8);
            $table->bigInteger('amount');
            $table->bigInteger('discount')->nullable();
            $table->bigInteger('total_amount');
            $table->string('pdf_url');
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
        Schema::dropIfExists('couriers');
    }
}
