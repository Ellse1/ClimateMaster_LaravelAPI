<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicUserProfileResource;
use App\PublicUserProfile;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicUserProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:user,admin');
    }


    /**
     * Return the public Profile of the current user
     */
    public function getPublicUserProfile(Request $request){
        $user = User::find(auth()->user()->id);

        $publicUserProfile = $user->public_user_profile;

        if($publicUserProfile == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat noch kein Öffentliches Profil'
            ]);
        }
        
        return (new PublicUserProfileResource($publicUserProfile))->additional([
            'state' => 'success',
            'message' => 'Das PublicUserProfile wurde erfolgreich zurück gegeben'
        ]);

    }

    //Change the value of 'public'. If there is no PublicUserProfile in Database until now -> create one
    public function changePublic(Request $request){
        $validator = Validator::make($request->all(), [
            'public' => 'required|boolean'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde kein valider Wert für public mitgegeben'
            ]);
        }

        $user = User::find(auth()->user()->id);
        $publicUserProfile = $user->public_user_profile;
        
        //If null -> create new one (with the value of public of request) and return this.
        if($publicUserProfile == null){
            $publicUserProfile = new PublicUserProfile();
            $publicUserProfile->user_id = $user->id;
        }   

        $publicUserProfile->public = $request->public;
        $publicUserProfile->save();

        return (new PublicUserProfileResource($publicUserProfile))->additional([
            'state' => 'success',
            'message' => 'Die Sichtbarkeit des öffentlichen Profils wurde erfolgreich geändert.'
        ]);
    }




    /**
     * Updates the information for the publicUserProfile
     */
    public function update(Request $request){
        $user = User::find(auth()->user()->id);
        $publicUserProfile = $user->public_user_profile;

        if($publicUserProfile == null){
            return response()->json([
                'state' => 'success',
                'message' => 'Der angemeldete Benutzer hat kein öffentliches Profil'
            ]);
        }


        $publicUserProfile->information_general = $request->information_general;
        $publicUserProfile->information_heating_electricity = $request->information_heating_electricity;
        $publicUserProfile->information_mobility = $request->information_mobility;
        $publicUserProfile->information_nutrition = $request->information_nutrition;
        $publicUserProfile->information_consumption = $request->information_consumption;
        $publicUserProfile->information_public_emissions = $request->information_public_emissions;
        $publicUserProfile->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Das öffentliche Profil wurde erfolgreich gespeichert.'
        ]);
    }

}
