<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessDtlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bank_dtls_id');
            $table->string('firm_name');
            $table->string('territory_areas');
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->integer('turnover')->nullable();
            $table->integer('manpower');
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
        Schema::dropIfExists('business_dtls');
    }
}
