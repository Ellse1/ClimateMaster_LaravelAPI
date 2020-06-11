<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibleAndIconToClimadviceChecks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('climadvice_checks', function (Blueprint $table) {
            $table->string('icon_name')->after('button_send_text')->nullable();
            $table->boolean('visible')->after('icon_name')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('climadvice_checks', function (Blueprint $table) {
            //
        });
    }
}
