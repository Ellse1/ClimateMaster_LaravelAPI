<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicUserProfileResource;
use App\Http\Resources\UserForPublicUserProfileList;
use App\PublicUserProfile;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class PublicUserProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:user,admin', ['except' => ['getAllWithCalculationAndProfilePicture']]);
    }

    /**
     * Returns all public profiles (public = true), to show in slideshow
     * Required: ProfilePicture, at least 1 CO2Calculation, ClimateMasterStatement
     */
    public function getAllWithCalculationAndProfilePicture(Request $request){

        // Get all public profiles
        // $publicUserProfiles = PublicUserProfile::where('public', true)->with('user')->get();

        $climateMasterUsers = User::where('profile_picture_name', '!=', null)
            ->whereHas('climatemasters', function (Builder $query) {
                $query->where('verified', true);
            })
            ->whereHas('public_user_profile', function (Builder $query) {
                $query->where('public', true);
            })
            ->orderBy('created_at')
            ->get();    
                
        $notClimateMasterUsers = User::where('profile_picture_name', '!=', null)
            ->whereHas('climatemasters', function (Builder $query) {
                $query->where('verified', false);
            })
            ->whereHas('public_user_profile', function (Builder $query) {
                $query->where('public', true);
            })
            ->orderBy('created_at')
            ->get();    
        
        $usersToReturn = $climateMasterUsers->merge($notClimateMasterUsers);
                

            // $userForPublicProfileListCollection = UserForPublicUserProfileList::collection($users)->sortBy('climatemaster_state');


        return (UserForPublicUserProfileList::collection($usersToReturn))->additional([
            'state' => 'success',
            'message' => 'Es wurden alle Öffentliche Profile zurückgegeben, die auf öffentlich geschaltet wurden, mindestens eine Berechnung haben und ein Profilbild haben.'
        ]);

    }




    /**
     * Return the public Profile of the current user
     */
    public function getPublicUserProfile_ByCurrentUser(Request $request){
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
    public function changePublic_ByCurrentUser(Request $request){
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

        if($request->public){
            return (new PublicUserProfileResource($publicUserProfile))->additional([
                'state' => 'success',
                'message' => 'Das Profil ist jetzt öffentlich.'
            ]);
        }

        return (new PublicUserProfileResource($publicUserProfile))->additional([
            'state' => 'success',
            'message' => 'Das Profil ist jetzt privat.'
        ]);


    }


    /**
     * Updates the information for the publicUserProfile
     */
    public function updatePublicUserProfile_ByCurrentUser(Request $request){
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
