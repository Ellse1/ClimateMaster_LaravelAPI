<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CO2Calculation extends Model
{

    protected $table="co2calculations";

    protected $casts = [
        'public_emissions' => 'decimal:2',
        'consumption' => 'decimal:2',
        'nutrition' => 'decimal:2',
        'mobility' => 'decimal:2',
        'heating_electricity' => 'decimal:2',
        'prevention_at_others' => 'decimal:2',
        'total_emissions' => 'decimal:2'
    ];

    //Gets the ClimateMaster, this calculation belongs to
    public function climatemaster(){
        return $this->belongsTo(Climatemaster::class);
    }
}
