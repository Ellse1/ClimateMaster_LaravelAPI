<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Climatemaster_steps_completed extends Model
{
    protected $table = "climatemaster_steps_completed";


    public function user(){
        return $this->belongsTo(User::class);
    }
}
