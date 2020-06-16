<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicUserProfileResource;
use App\Http\Resources\UserForPublicUserProfileList_badPictureQuality_Resource;
use App\Http\Resources\UserForPublicUserProfileList_goodPictureQuality_Resource;
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
        //first the ClimateMasters
        $climateMasterUsers = User::where('profile_picture_name', '!=', null)
            ->whereHas('climatemasters', function (Builder $query) {
                $query->where('verified', true);
            })
            ->whereHas('public_user_profile', function (Builder $query) {
                $query->where('public', true);
            })
            ->orderBy('created_at')
            ->get(); 

        //than the people not beeing climatemasters
        //but having (co2calculation and public_user_profile with public ) or (climadvice_user_checks with active and public_user_profile with public and public_climadvice_checks)
        //having a profile picture is allways necessary
        $notClimateMasterUsers = User::
        where(function ($userQuery){
            $userQuery->whereHas('climadvice_user_checks', function (Builder $qu){  //co2calculation and public_user_profile with public 
                $qu->where('active', true);
            })->whereHas('public_user_profile', function (Builder $query) {         //(climadvice_user_checks with active and public_user_profile with public and public_climadvice_checks)
                $query->where('public', true)->where('public_climadvice_checks', true);
            });
        })->orWhere(function ($userQue){
            $userQue->whereHas('climatemasters', function (Builder $query) {        
                $query->where('verified', false)                                 
                    ->has('co2calculations');                                   
                });
        })
        ->where('profile_picture_name', '!=', null)       
        ->orderBy('created_at')
        ->get();     
            

        
        $usersToReturn = $climateMasterUsers->merge($notClimateMasterUsers);
                

        //Check if i should compromise the profile picture images -> load faster but only bad image quality
        if($request->compress == true){
            return (UserForPublicUserProfileList_badPictureQuality_Resource::collection($usersToReturn))->additional([
                'state' => 'success',
                'message' => 'Es wurden alle Öffentliche Profile zurückgegeben, die auf öffentlich geschaltet wurden, mindestens eine Berechnung haben und ein Profilbild haben.'
            ]);
        }

        return (UserForPublicUserProfileList_goodPictureQuality_Resource::collection($usersToReturn))->additional([
            'state' => 'success',
            'message' => 'Es wurden alle Öffentliche Profile zurückgegeben, die auf öffentlich geschaltet wurden, mindestens eine Berechnung haben und ein Profilbild haben.'
        ]);

    }




    /**
     * Return the public Profile of the current user
     * -> if there is no public user profile -> create one
     */
    public function getPublicUserProfile_ByCurrentUser(Request $request){
        $user = User::find(auth()->user()->id);

        $publicUserProfile = $user->public_user_profile;

        if($publicUserProfile == null){
            $publicUserProfile_new = new PublicUserProfile();
            $publicUserProfile_new->user_id = $user->id;
            $publicUserProfile_new->public = false;
            $publicUserProfile_new->save();
            $publicUserProfile = $publicUserProfile_new;
        }
        
        return (new PublicUserProfileResource($publicUserProfile))->additional([
            'state' => 'success',
            'message' => 'Das PublicUserProfile wurde erfolgreich zurück gegeben'
        ]);

    }

    //Change the value of 'public'. If there is no PublicUserProfile in Database until now -> create one
    public function changePublic_ByCurrentUser(Request $request){
        $validator = Validator::make($request->all(), [
            'public' => 'required|boolean',
            'public_climadvice_checks' => 'required|boolean',
            'public_social_media_names' => 'required|boolean',
            'public_pictures' => 'required|boolean'
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
        $publicUserProfile->public_climadvice_checks = $request->public_climadvice_checks;
        $publicUserProfile->public_social_media_names = $request->public_social_media_names;
        $publicUserProfile->public_pictures = $request->public_pictures;
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
        $publicUserProfile->information_compensation = $request->information_compensation;
        $publicUserProfile->information_public_emissions = $request->information_public_emissions;
        $publicUserProfile->save();

        return (new PublicUserProfileResource($publicUserProfile))->additional([
            'state' => 'success',
            'message' => 'Das öffentliche Profil wurde erfolgreich gespeichert.'
        ]);
    }

}
