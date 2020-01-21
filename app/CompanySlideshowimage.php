<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanySlideshowimage extends Model
{
    protected $table = 'company_slideshowimages';

    protected $fillable = ["company_id", "caption"];

    public function company(){
        return $this->belongsTo(Company::class);
    }

}
