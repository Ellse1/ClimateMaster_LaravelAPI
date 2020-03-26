<?php

namespace App\Http\Controllers;

use App\Http\Resources\Climatemaster_steps_completedResource;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Climatemaster_steps_completedController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth.role:user,admin");
    }

    /**
     * Returns the climatemaster_steps_completed model of the currenc year and the current user
     */
    public function getClimatemaster_steps_completed_ByCurrentUser(){
        $user = User::find(auth()->user()->id);

        $climatemaster_steps_completed = $user->climatemaster_steps_completed()->where('year', Carbon::now()->year)->first();

        if($climatemaster_steps_completed == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es ist noch kein climatemaster_steps_completed Model für dieses Jahr vorhanden'
            ]);
        }

        return (new Climatemaster_steps_completedResource($climatemaster_steps_completed))->additional([
            'state' => 'success',
            'message' => 'Das aktuelle climatemaster_steps_completed Model wurde erfolgreich zurückgegeben'
        ]);
    }


    public function reduceShortTermCompleted_ByCurrentUser(){
        $user = User::find(auth()->user()->id);
        $climatemaster_steps_completed = $user->climatemaster_steps_completed()->where('year', Carbon::now()->year)->first();
        
        if($climatemaster_steps_completed == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es ist noch kein climatemaster_steps_completed Model für dieses Jahr vorhanden'
            ]);
        }

        // If the first step is not completed
        if($climatemaster_steps_completed->calculate != true){
            return response()->json([
                'state' => 'error',
                'message' => 'Es muss erst der vorherige Schritt abgeschlossen werden.'
            ]);
        }


        $climatemaster_steps_completed->reduce_short_term = true;
        $climatemaster_steps_completed->save();

        return (new Climatemaster_steps_completedResource($climatemaster_steps_completed))->additional([
            'state' => 'success',
            'message' => 'Herzlichen Glückwunsch. Schritt erfolgreich abgeschlossen.'
        ]);

    }

    public function customizeCalculationCompleted_ByCurrentUser(){
        $user = User::find(auth()->user()->id);
        $climatemaster_steps_completed = $user->climatemaster_steps_completed()->where('year', Carbon::now()->year)->first();
        
        if($climatemaster_steps_completed == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es ist noch kein climatemaster_steps_completed Model für dieses Jahr vorhanden'
            ]);
        }

        // If the first 2 steps are not completed
        if($climatemaster_steps_completed->reduce_short_term != true || $climatemaster_steps_completed->calculate != true){
            return response()->json([
                'state' => 'error',
                'message' => 'Es müssen erst die vorherigen Schritte abgeschlossen werden.'
            ]);
        }

        $climatemaster_steps_completed->customize_calculation = true;
        $climatemaster_steps_completed->save();

        return (new Climatemaster_steps_completedResource($climatemaster_steps_completed))->additional([
            'state' => 'success',
            'message' => 'Herzlichen Glückwunsch. Schritt erfolgreich abgeschlossen.'
        ]);

    }
}
