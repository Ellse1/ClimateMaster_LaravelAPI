<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClimadviceChangeImageToIconAddClimateMasterAreaAddEasy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //From now on iconName is for font-awesome-icons not for image.png
        Schema::table('climadvices', function (Blueprint $table) {
            $table->boolean('easy');
            $table->string('climateMasterArea');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('climadvices', function (Blueprint $table) {
            $table->dropColumn('easy');
            $table->dropColumn('climateMasterArea');
        });
    }
}
