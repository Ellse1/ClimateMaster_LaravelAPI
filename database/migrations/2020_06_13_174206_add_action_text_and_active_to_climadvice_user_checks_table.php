<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionTextAndActiveToClimadviceUserChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('climadvice_user_checks', function (Blueprint $table) {
            $table->string('action_text')->after('question_answer')->nullable();
            $table->boolean('active')->after('action_text')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('climadvice_user_checks', function (Blueprint $table) {
            $table->dropColumn('action_text');
            $table->dropColumn('active');
        });
    }
}
