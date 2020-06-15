<?php

namespace App\Http\Controllers;

use App\Climadvice;
use App\Http\Resources\ClimadviceResource;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

//toTestMail
use Illuminate\Support\Facades\Mail;


class ClimadviceController extends Controller
{

    //Constructor:
    public function __construct()
    {
        $this->middleware('auth.role:user', ['except' => ['getAllClimadvices', 'getClimadvices_with_ClimadviceUserChecks_ForPublicProfile_ByUsername']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllClimadvices()
    {
        return (ClimadviceResource::collection(climadvice::all()))
        ->additional([
            'state' => 'success',
            'message' => 'Erfolgreich alle Climadvices zurückgegeben.'
        ]);
    }


    /**
     * Get the Climadvices with ClimadviceUserChecks to show in /_username (public profile of user)
     */
    public function getClimadvices_with_ClimadviceUserChecks_ForPublicProfile_ByUsername(Request $request){
        
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:users,username'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde kein valider Benutzername angegeben.'
            ]);
        }
        //get all climadviceUserChecks of this user
        $user = User::where('username', $request->username)->first();
    
        //check if user has a public profile and if public is enabled
        $publicProfile = $user->public_user_profile;
        if($publicProfile == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat kein öffentliches Profil.'
            ]);            
        }
        else if($publicProfile->public == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat sein Profil auf privat geschalten.'
            ]);             
        }
        //Check if 'climadviceChecks' are public
        if($publicProfile->public_climadvice_checks != true){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat seine ClimadviceChecks auf privat geschalten.'
            ]);   
        }
        

        $user_id = $user->id;

        $climadviceWithClimadviceUserChecks_OfUsername = Climadvice::with(['climadvice_checks' => function($q) use($user_id){
            $q->whereHas('climadvice_user_checks', function($qu) use($user_id){
                $qu->where('active', true)->where('user_id', $user_id);   
            }
            )->with('climadvice_user_checks')
            ;
        }
        ])->whereHas('climadvice_user_checks', function($qu) use($user_id){
            $qu->where('active', true)->where('user_id', $user_id);   
        }
        )
        ->get();
     

        return response()->json([
            'data' => $climadviceWithClimadviceUserChecks_OfUsername,
            'state' => 'success',
            'message' => 'Alle ClimadviceChecks mit information zu den ClimadviceChecks zurück gegeben.'
        
        ]);

    }

    /**
     * Return the climadvice with ClimadviceChecks and ClimadviceUserChecks to show them in "myProfile?page=climadviceChecks"
     */
    public function getClimadvices_with_ClimadviceUserChecks_ByCurrentUser(Request $request){

        //get all climadviceUserChecks of this user
        $user = User::find(auth()->user()->id);
           
        $user_id = $user->id;
        $climadviceWithClimadviceUserChecks_OfUsername = Climadvice::with(['climadvice_checks' => function($q) use($user_id){
            $q->whereHas('climadvice_user_checks', function($qu) use($user_id){
                $qu->where('active', true)->where('user_id', $user_id);   
            }
            )->with('climadvice_user_checks')
            ;
        }
        ])->whereHas('climadvice_user_checks', function($qu) use($user_id){
            $qu->where('active', true)->where('user_id', $user_id);   
        }
        )
        ->get();
     

        return response()->json([
            'data' => $climadviceWithClimadviceUserChecks_OfUsername,
            'state' => 'success',
            'message' => 'Alle ClimadviceChecks mit information zu den ClimadviceChecks zurück gegeben.'
        
        ]);

    }


}
