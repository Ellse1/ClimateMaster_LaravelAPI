<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalEmissionsToCo2calculationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('co2calculations', function (Blueprint $table) {
            $table->float('total_emissions', 5, 2)->nullable()->after('prevention_at_others');
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
            $table->dropColumn('total_emissions');
        });
    }
}
