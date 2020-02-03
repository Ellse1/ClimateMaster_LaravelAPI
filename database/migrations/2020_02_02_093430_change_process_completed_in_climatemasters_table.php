<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProcessCompletedInClimatemastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('climatemasters', function (Blueprint $table) {
            $table->renameColumn('processCompletet', 'process_completed');
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
            $table->renameColumn('process_completed', 'processCompletet');           
        });
    }
}
