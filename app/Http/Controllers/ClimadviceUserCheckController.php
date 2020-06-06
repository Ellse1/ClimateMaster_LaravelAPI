<?php

namespace App\Http\Controllers;

use App\ClimadviceUserCheck;
use App\Http\Resources\ClimadviceUserCheckResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClimadviceUserCheckController extends Controller
{
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
        
        //check if there was sent an answer to the question
        if($request->question_answer != null){
            $climadviceUserCheck->question_answer = $request->question_answer;
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
}
