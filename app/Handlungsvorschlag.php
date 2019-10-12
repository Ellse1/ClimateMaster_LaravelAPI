<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Handlungsvorschlag extends Model
{
    //name of table
    protected $table = 'handlungsvorschlag';
    //to allow create a Handlungsvorschlag with "Handlungsvorschlag::create"
    protected $fillable = ['titel', 'kurzbeschreibung', 'detailbeschreibung', 'iconName'];
}
