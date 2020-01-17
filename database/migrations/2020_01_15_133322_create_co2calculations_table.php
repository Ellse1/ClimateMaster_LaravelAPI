<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCo2calculationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('co2calculations', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->unsignedBigInteger('climatemaster_id');
            $table->foreign('climatemaster_id')->references('id')->on('climatemasters');
            $table->string('link_uba_co2calculation');
            $table->float('heatingElectricity', 5, 2)->nullable();
            $table->float('mobility', 5, 2)->nullable();
            $table->float('nutrition', 5, 2)->nullable();
            $table->float('consumption', 5, 2)->nullable();
            $table->float('public_emissions', 5, 2)->nullable();
            $table->float('prevention_at_others', 5, 2)->nullable();
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
        Schema::dropIfExists('co2calculations');
    }
}
