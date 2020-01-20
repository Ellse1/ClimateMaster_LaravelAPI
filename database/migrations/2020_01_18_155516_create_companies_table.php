<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->string('climadvice_name');
            $table->string('name');
            $table->mediumText('description')->nullable();
            $table->string('email')->nullable();
            $table->string('street');
            $table->string('house_number');
            $table->integer('postcode');
            $table->string('residence');
            $table->string('logo_image_name')->nullable();
            $table->string('header_image_name')->nullable();
            $table->boolean('verified')->default(false);
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
        Schema::dropIfExists('companies');
    }
}
