<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClimadviceCheck extends Model
{
    /**
     * Return the userChecks, of this climadviceCheck ->
     * to return all the climadvices with the userChecks of one user -> for public profile
     */
    public function climadvice_user_checks(){
        return $this->hasMany(ClimadviceUserCheck::class);
    }}
