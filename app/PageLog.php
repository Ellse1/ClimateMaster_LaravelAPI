<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PageLog extends Model
{
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y.m.d');
    }
    public function getUpdatedAtAttribute($value){
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y.m.d');
    }
}
