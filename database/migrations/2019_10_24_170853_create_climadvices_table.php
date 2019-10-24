<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClimadvicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('climadvices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('shortDescription');
            $table->mediumText('detailedDescription');
            $table->string('iconName');
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
        Schema::dropIfExists('climadvices');
    }
}
