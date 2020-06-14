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
        $this->middleware('auth.role:admin', ['except' => ['getAllClimadvices', 'getClimadvices_with_ClimadviceUserChecks_ForPublicProfile_ByUsername']]);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeClimadvice(Request $request)
    {
        //Check if user is logged in
        if(Auth::check() == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Du bist nicht eingeloggt'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:climadvices,name',
            'title' => 'required|unique:climadvices,title',
            'shortDescription' => 'required|unique:climadvices,shortDescription|max:200',
            'iconName' => 'required',
            'easy' => 'required',
            'climateMasterArea' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        
        $climadvice = Climadvice::create([
            'name' => $request->name,
            'title' => $request->title,
            'shortDescription' => $request->shortDescription,
            'iconName' => $request->iconName,
            'easy' => (int)$request->easy,
            'climateMasterArea' => $request->climateMasterArea
        ]);

        return (new ClimadviceResource($climadvice))
            ->additional([
                'state' => 'success',
                'message' => 'Climadvice erfolgreich erstellt'
            ]);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateClimadvice_ByClimadviceID(Request $request)
    {
        //Check if user is logged in
        if(Auth::check() == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Du bist nicht eingeloggt'
            ]);
        }

        //validate
        $validator = Validator::make($request->all(), [
            'id' => "required",
            'name' => "required",
            'title' => "required",
            'shortDescription' => "required",
            'iconName' => "required",
            'easy' => "required",
            'climateMasterArea' => "required"
        ]);


        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $climadvice = Climadvice::find($request->id);
        $climadvice->title = $request->title;
        $climadvice->shortDescription = $request->shortDescription;
        $climadvice->iconName = $request->iconName;
        $climadvice->easy = (int)$request->easy;
        $climadvice->climateMasterArea = $request->climateMasterArea;
        $climadvice->save();

        return (new ClimadviceResource($climadvice))
            ->additional([
                'state' => 'success',
                'message' => 'Climadvice erfolgreich geändert'
            ]);

    }


     /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyClimadvice_ByClimadviceID(Request $request)
    {
        if(Auth::check() == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Du bist nicht eingeloggt'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        //Remove Image
        $climadvice = Climadvice::find($request->id);

        $deleted =  $climadvice->forceDelete();

        return response()->json([
            'state' => 'success',
            'message' => 'Climadvice erfolgreich gelöscht'
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

        $climadviceWithClimadviceUserChecks_OfUsername = Climadvice::with(['climadvice_checks.climadvice_user_checks' => function($qu) use($user_id){
            $qu->where('user_id', $user_id);
        }])
        ->whereHas('climadvice_user_checks', function($query) use($user_id){
                $query->where('user_id', $user_id);
              })
        ->get();
        
        
        // ->whereHas('climadvice_user_checks', function($query) use($user_id){
        //     $query->where('user_id', $user_id);
        //  })->get();
        
        // ::with(['climadvice_user_checks' => function($q) use($user_id){
        //     $q->where('user_id', $user_id);
        // }])
        // ->whereHas('climadvice_user_checks', function($query) use($user_id){
        //     $query->where('user_id', $user_id);
        //  })->get();

        return response()->json([
            'data' => $climadviceWithClimadviceUserChecks_OfUsername,
            'state' => 'success',
            'message' => 'Alle ClimadviceUserChecks mit information zu den ClimadviceChecks zurück gegeben.'
        
        ]);

    }




}
