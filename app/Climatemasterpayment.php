<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Climatemasterpayment extends Model
{
    /**
     * Return the one user, this Climatemasterpayment has
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * Return the invoices (normaly only one), this payment has
     */
    public function invoices(){
        return $this->hasMany(Invoice::class);
    }
}
