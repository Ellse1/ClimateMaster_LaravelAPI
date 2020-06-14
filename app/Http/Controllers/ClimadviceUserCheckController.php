<?php

namespace App\Http\Controllers;

use App\Climadvice;
use App\ClimadviceCheck;
use App\ClimadviceUserCheck;
use App\Http\Resources\ClimadviceUserCheckResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;

class ClimadviceUserCheckController extends Controller
{

    public function __construct()
    {
        $this->middleware("auth.role:user,admin", ["except" => ["store"]]);
    }

    /**
     * Get all climadviceUserhecks of current User
     */
    public function getClimadviceUserChecks_ByCurrentUser(Request $request){
        //get all climadviceUserChecks of this user
        $user = User::find(auth()->user()->id);
        $climadviceUserChecks_ofUser = $user->climadvice_user_checks()->where('active', true)->get();

        //get all climadviceChecks
        $climadviceChecks = ClimadviceCheck::all();

        $climadviceUserChecks_toReturn = collect();

        //get only the latest climadviceUserCheck of each climadviceCheck
        foreach($climadviceChecks as $climadviceCheck){
            $climadviceUserChecks_of_climadviceCheck = $climadviceUserChecks_ofUser->where('climadvice_check_id', $climadviceCheck->id)->sortByDesc('created_at');
            if($climadviceUserChecks_of_climadviceCheck != null){
                $climadviceUserChecks_toReturn->push($climadviceUserChecks_of_climadviceCheck->first());
            }
        }

        return (ClimadviceUserCheckResource::collection($climadviceUserChecks_toReturn))->additional([
            'state' => 'success',
            'message' => 'Es wurden die aktuellsten ClimadviceUserChecks des aktuellen Benutzers zurück gegeben.'
        ]);
    }


    /**
     * save a ClimadviceUserCheck
     */
    public function store(Request $request){
        
        $validator = Validator::make($request->all(), [
            'climadvice_id' => 'required|exists:climadvices,id',
            'climadvice_check_id' => 'required|exists:climadvice_checks,id',
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurden falsche Parameter mitgegeben: ' + $validator->errors()
            ]);
        }


        //new climadviceUserCheck
        $climadviceUserCheck = new ClimadviceUserCheck();
        $climadviceUserCheck->climadvice_id = $request->climadvice_id;
        $climadviceUserCheck->climadvice_check_id = $request->climadvice_check_id;
        
        //get the climadviceCheck -> to create the right action_text
        $climadviceCheck = ClimadviceCheck::find($request->climadvice_check_id);

        //check if there was sent an answer to the question
        if($request->question_answer != null){
            $climadviceUserCheck->question_answer = $request->question_answer;
            $climadviceUserCheck->action_text = $climadviceCheck->button_send_text . ' ' . $request->question_answer;
        }
        else{
            $climadviceUserCheck->action_text = $climadviceCheck->action;
        }
        //check if user is signed in
        if(auth()->user() != null){
            $user_id = auth()->user()->id;
            $climadviceUserCheck->user_id = $user_id;
        }
        //if question_answer = null and user is not logged in
        if(auth()->user() == null && $request->question_answer == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine Antwort eingegeben und der Benutzer ist nicht angemeldet. Der ClimadviceUserCheck wurde nicht gespeichert.'
            ]);
        }

        $climadviceUserCheck->save();

        return (new ClimadviceUserCheckResource($climadviceUserCheck))->additional([
            'state' => 'success',
            'message' => 'Der ClimadviceUserCheck wurde erfolgreich gespeichert.'
        ]);

    }  


    /**
     * Delete all climadviceUserChecks of a climadviceCheck of current User -> a user doesn't do a climadviceCheck anymore
     */
    public function deactivateAllOfClimadviceCheck_ByCurrentUser(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:climadvice_checks,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine valide id mitgegeben. ' . $validator->errors()
            ]);
        }

        $user_id = auth()->user()->id;
        //get all the climadviceUserChecks of this climadviceCheck of this user
        $climadvice_user_checks = ClimadviceUserCheck::where('climadvice_check_id', $request->id)
        ->where('user_id', $user_id)
        ->get();

        //deactivate all climadviceUserCheks
        foreach($climadvice_user_checks as $climadviceUserCheck){
            $climadviceUserCheck->active = false;
            $climadviceUserCheck->save();
        }

        // $climadvice_user_check->delete();
        return response()->json([
            'state' => 'success',
            'message' => 'Der ClimadviceCheck wurde erfolgreich entfernt'
        ]);

    }


}
