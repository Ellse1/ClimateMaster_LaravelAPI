<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Climadvice extends Model
{

    /**
     * Return the climadviceChecks, of this climadvice ->
     * to return all the climadvices with the climadviceUserChecks of one user -> for public profile
     */
    public function climadvice_checks(){
        return $this->hasMany(ClimadviceCheck::class);
    }

    /**
     * Return the userChecks, of this climadvice ->
     * to return all the climadvices with the userChecks of one user -> for public profile
     */
    public function climadvice_user_checks(){
        return $this->hasMany(ClimadviceUserCheck::class);
    }

    //name of table
    protected $table = 'climadvices';
    //to allow create a climadvice with "climadvice::create"
    protected $fillable = ['name', 'title', 'shortDescription', 'iconName', 'easy', 'climateMasterArea', 'created_at', 'updated_at'];
}
