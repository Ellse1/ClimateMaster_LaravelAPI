<?php

namespace App\Http\Controllers;

use App\ClimadviceCheck;
use App\Http\Resources\ClimadviceCheckResource;
use Illuminate\Http\Request;

class ClimadviceCheckController extends Controller
{

    /**
     * Returns all the climadviceChecks
     */
    public function getAllClimadviceChecks(){
        return (ClimadviceCheckResource::collection(ClimadviceCheck::all()))
        ->additional([
            'state' => 'success',
            'message' => 'Erfolgreich alle ClimadviceChecks zurückgegeben.'
        ]);
    }  
}
