<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicUserProfile extends Model
{
    //Returns the user, this publicUserProfile belongs to
    public function user(){
        return $this->belongsTo(User::class);
    }
}
