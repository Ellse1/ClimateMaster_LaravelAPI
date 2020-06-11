<?php

namespace App\Http\Controllers;

use App\ClimadviceCheck;
use App\Http\Resources\ClimadviceCheckResource;
use Illuminate\Http\Request;

class ClimadviceCheckController extends Controller
{

    /**
     * Returns the visible climadviceChecks
     */
    public function getVisibleClimadviceChecks(){
        return (ClimadviceCheckResource::collection(ClimadviceCheck::where('visible', true)->get()))
        ->additional([
            'state' => 'success',
            'message' => 'Erfolgreich alle sichtbaren ClimadviceChecks zurückgegeben.'
        ]);
    }  

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
