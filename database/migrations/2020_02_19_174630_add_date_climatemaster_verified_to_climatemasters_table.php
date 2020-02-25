<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateClimatemasterVerifiedToClimatemastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('climatemasters', function (Blueprint $table) {
            $table->timestamp('date_climatemaster_verified')->after('verified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('climatemasters', function (Blueprint $table) {
            $table->dropColumn('date_climatemaster_verified');
        });
    }
}
