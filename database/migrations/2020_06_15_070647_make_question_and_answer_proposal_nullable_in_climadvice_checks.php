<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeQuestionAndAnswerProposalNullableInClimadviceChecks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('climadvice_checks', function (Blueprint $table) {
            $table->string('question')->nullable()->change();
            $table->string('answer_proposal')->nullable()->change();
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
            $table->string('question')->nullable(false)->change();
            $table->string('answer_proposal')->nullable(false)->change();
        });
    }
}
