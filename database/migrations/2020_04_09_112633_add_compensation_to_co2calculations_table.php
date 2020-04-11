<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompensationToCo2calculationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('co2calculations', function (Blueprint $table) {
            $table->float('compensation', 5, 2)->nullable()->after('prevention_at_others');
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
            $table->dropColumn('compensation');
        });
    }
}
