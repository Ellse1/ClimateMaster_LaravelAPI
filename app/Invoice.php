<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * Returns the one Payment, this Invoice has
     */
    public function climatemasterpayment(){
        return $this->belongsTo(Climatemasterpayment::class);
    }
}
