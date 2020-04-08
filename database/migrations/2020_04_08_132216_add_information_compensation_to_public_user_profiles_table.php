<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInformationCompensationToPublicUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('public_user_profiles', function (Blueprint $table) {
            $table->text('information_compensation')->nullable()->after('information_public_emissions');//Why does the user compensate it's emissions?
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('public_user_profiles', function (Blueprint $table) {
            $table->dropColumn('information_compensation');
        });
    }
}
