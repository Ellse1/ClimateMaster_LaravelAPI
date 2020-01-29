<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClimatemasterStepsCompletedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('climatemaster_steps_completed', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('year');
            $table->boolean('calculate')->default(false);
            $table->boolean('reduce_short_term')->default(false);
            $table->boolean('customize_calculation')->default(false);
            $table->boolean('become_climatemaster')->default(false);
            $table->boolean('present_progress')->default(false);
            $table->boolean('reduce_long_term')->default(false);
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
        Schema::dropIfExists('climatemaster_steps_completed');
    }
}
