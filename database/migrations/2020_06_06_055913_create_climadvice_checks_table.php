<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClimadviceChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('climadvice_checks', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->unsignedBigInteger('climadvice_id');
            $table->foreign('climadvice_id')->references('id')->on('climadvices');
            $table->string('action'); //Wir kaufen Ökostrom
            $table->string('question');//Bei welchem Ökostromanbieter bezieht euer Haushalt Ökostrom?
            $table->string('answer_proposal');//Greenpeace Energy -> this will be shown as placeholder
            $table->string('button_send_text');
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
        Schema::dropIfExists('climadvice_checks');
    }
}
