<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandlungsvorschlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('handlungsvorschlag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('titel');
            $table->string('kurzbeschreibung');
            $table->mediumText('detailbeschreibung');
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
        Schema::dropIfExists('handlungsvorschlag');
    }
}
