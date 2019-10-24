<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Climadvice extends Model
{
    //name of table
    protected $table = 'climadvices';
    //to allow create a climadvice with "climadvice::create"
    protected $fillable = ['title', 'shortDescription', 'detailedDescription', 'iconName'];
}
