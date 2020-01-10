<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $table="blogposts";
    protected $fillable=['heading', 'previewContent', 'postContent', 'imageName'];



    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y.m.d');
    }
    public function getUpdatedAtAttribute($value){
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y.m.d');
    }

}
