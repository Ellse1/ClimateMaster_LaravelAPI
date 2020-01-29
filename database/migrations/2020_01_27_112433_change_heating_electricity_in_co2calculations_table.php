<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeHeatingElectricityInCo2calculationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('co2calculations', function (Blueprint $table) {
            $table->renameColumn('heatingElectricity', 'heating_electricity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('co2calculations', function (Blueprint $table) {
            $table->renameColumn('heating_electricity', 'heatingElectricity');
        });
    }
}
