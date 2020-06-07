<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublishInformationToPublicUserProfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('public_user_profiles', function (Blueprint $table) {
            $table->boolean('public_climadvice_checks')->after('public')->default(false);
            $table->boolean('public_social_media_names')->after('public_climadvice_checks')->default(false);
            $table->boolean('public_pictures')->after('public_social_media_names')->default(false);

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
            $table->dropColumn('public_climadvice_checks');
            $table->dropColumn('public_social_media_names');
            $table->dropColumn('public_pictures');
        });
    }
}
