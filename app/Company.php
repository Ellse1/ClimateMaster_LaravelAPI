<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $fillable = ['climadvice_name', 'name', 'street', 'house_number', 'postcode', 'residence', 'email'];
    //
    public function users(){
        return $this->belongsToMany(User::class);
    }
}
