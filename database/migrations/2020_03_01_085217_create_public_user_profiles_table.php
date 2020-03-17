<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_user_profiles', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('public')->default(false);
            $table->text('information_general')->nullable();//Why is the person ClimateMaster
            $table->text('information_heating_electricity')->nullable();
            $table->text('information_mobility')->nullable();
            $table->text('information_nutrition')->nullable();
            $table->text('information_consumption')->nullable();
            $table->text('information_public_emissions')->nullable();
            $table->text('information_prevention_at_others')->nullable();
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
        Schema::dropIfExists('public_user_profiles');
    }
}
