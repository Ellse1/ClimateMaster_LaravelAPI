<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Climatemaster extends Model
{

    protected $dates = ['date_climatemaster_verified'];

    //Returns the user of this Climatemaster
    public function user(){
        return $this->belongsTo(User::class);
    }

    //return the co2 calculations this climatemaster has
    public function co2calculations(){
        return $this->hasMany(CO2Calculation::class);
    }
}
