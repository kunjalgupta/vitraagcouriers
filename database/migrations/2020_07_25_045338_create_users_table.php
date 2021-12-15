<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('role');
            $table->string('code_number')->nullable();
            $table->integer('office_address_id');
            $table->integer('resident_address_id');
            $table->integer('business_dtls_id');
            $table->string('name', 60);
            $table->integer('state');
            $table->string('father_name', 60);
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('aadhar_card')->nullable();
            $table->string('person_photo')->nullable();
            $table->string('pancard')->nullable();
            $table->string('lightbill')->nullable();
            $table->string('cheque')->nullable();
            $table->boolean('is_cargo_authorized')->default('0');
            $table->boolean('active_flag')->default('1');
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
        Schema::dropIfExists('users');
    }
}
