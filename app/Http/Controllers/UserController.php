<?php

namespace App\Http\Controllers;

use App\Company;
use App\PublicUserProfile;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use stdClass;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:user,admin', ['except' => ['isCompanyAdmin_ByCurrentUser', 'getDataToShowPublicUserProfile_ByUsername']]);
    }


    /**
     * Add profilePicture to user
     */
    public function addProfilePicture_ByCurrentUser(Request $request){
        
        $validator = Validator::make($request->all(), [
            'profilePicture' => 'required|image|mimes:jpeg,jpg,png,JPEG,JPG,PNG|max:6144' //max 6MB
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Format oder größe stimmen nicht. ' . $validator->errors()
            ]);
        }

        $user = auth()->user();
        $userID = $user ->id;
        $fileName = 'profilePicture' . $userID . '.' . $request->file('profilePicture')->getClientOriginalExtension();

        $path = $request->file('profilePicture')->storeAs("images/profilePictures", $fileName);

        $user->profile_picture_name = $fileName;
        $user->save();


        $image = Storage::get($path);

        return response()->json([
            'state' => 'success',
            'message' => 'Das Profilbild wurde erfolgreich gespeichert.',
            'image_base64' => base64_encode($image)
        ]);
        
    }

    public function getProfilePicture_ByCurrentUser(Request $request){
        $user = auth()->user();
        $filename = $user->profile_picture_name;
        if($filename != null){
            if(Storage::exists("/images/profilePictures/" . $filename)){
                $image = Storage::get("/images/profilePictures/" . $filename);
                return response()->json([
                    'state' => 'success',
                    'message' => 'Das Profilbild wurde erfolgreich zurückgegeben.',
                    'image_base64' => base64_encode($image)
                ]);
            }
            else{
                return response()->json([
                    'state' => 'error',
                    'message' => 'Profilbild nicht gefunden.'
                ]);
            }
        }else{
            return response()->json([
                'state' => 'error',
                'message' => 'Diese Person hat keine Profilbild.'
            ]);
        }
    }

    public function saveAddressAndSocialMediaInformation_ByCurrentUser(Request $request){
        $user = auth()->user();

        if($request->street != "undefined"){
            $user->street = $request->street;
        }

        if($request->house_number != "undefined"){
            $user->house_number = $request->house_number;
        }
        
        if($request->postcode != "undefined" && $request->postcode != null){
            error_log($request->postcode);
            //Validate -> number 
            $validator = Validator::make($request->all(), [
                'postcode' => 'required|integer'
            ]);
            if($validator->fails()){
                return response()->json([
                    'state' => 'error',
                    'message' => 'Postleitzahl muss eine gerade Zahl sein.'
                ]);
            }else{
                $user->postcode = $request->postcode;
            }
        }

        if($request->residence != "undefined"){
            $user->residence = $request->residence;
        }
        if($request->instagram_name != "undefined"){
            $user->instagram_name = $request->instagram_name;
        }
        if($request->facebook_name != "undefined"){
            $user->facebook_name = $request->facebook_name;
        }
        if($request->whatsapp_number != "undefined"){
            $user->whatsapp_number = $request->whatsapp_number;
        }

        $user->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Profildaten erfolgreich gespeichert.'
        ]);
    }



    /**
     * Looks if the current user is Admin for a given company
     * 
     * @params -> company_id
     */
    public function isCompanyAdmin_ByCurrentUser(Request $request){
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer|exists:companies,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Keine valide company_id: ' . $validator->errors()
            ]);
        }

        $company = Company::find($request->company_id);
        $userID = auth()->user()->id;
        $user = $company->users()->find($userID);

        if($user == null){
            return response()->json([
                'isCompanyAdmin' => false,
                'state' => 'success',
                'message' => 'Die Berechtigung wurde erfolgreich überprüft.'
            ]);
        }
        else{
            return response()->json([
                'isCompanyAdmin' => true,
                'state' => 'success',
                'message' => 'Die Berechtigung wurde erfolgreich überprüft.'
            ]);
        }

    }

    /**
     * check if the user should see the gratulation for becoming ClimateMaster
     */
    public function checkShowGratulationBecomingClimateMaster_ByCurrentUser(Request $request){
        $user = User::find(auth()->user()->id);
        $climatemaster = $user->climatemasters->where('year', Carbon::now()->year)->first();

        if($climatemaster == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Für diesen User gibt es noch keinen ClimateMaster für das aktuelle Jahr'
            ]);
        }

        //If he is not verified climatemaster
        if($climatemaster->verified == false){
            return response()->json([
                'state' => 'success',
                'message' => 'Der User wurde noch nicht als ClimateMaster für das aktuelle Jahr verifiziert.',
                'show_gratulation' => false
            ]);
        }

        $date_climatemaster_verified = $climatemaster->date_climatemaster_verified;
        $user_last_login = $user->logins()->latest()->skip(1)->first(); //take not the last login (this is the current) but one before

        if($user_last_login != null){
            //If he was verified as climatemaster, but didn't login since that -> show_gratulation!
            //last_login lessThan (date_climatemaster_verified)
            if($user_last_login->created_at->lt($climatemaster->date_climatemaster_verified)){
                return response()->json([
                    'state' => 'success',
                    'message' => 'Der User wurde als ClimateMaster für das aktuelle Jahr verifiziert, die Gratulation wurde noch nicht gesehen.',
                    'show_gratulation' => true
                ]);
            }
        }
        //since 28.03.20 'logins' is an own table, before that, it was only one 'last_login' column in 'users' table
        //If he is verified climatemaster, but has only one login entry (because he didn't login since the update is online)
        else{
            return response()->json([
                'state' => 'success',
                'message' => 'Der User wurde als ClimateMaster für das aktuelle Jahr verifiziert, die Gratulation wurde noch nicht gesehen.',
                'show_gratulation' => true
            ]);
        }

        //If he did login since that -> don't show gratulation again
        return response()->json([
            'state' => 'success',
            'message' => 'Der User hat die Gratulation schon gesehen.',
            'show_gratulation' => false
        ]);
    }





    //returns a datapackage with all the necessary data, to present one user
    public function getDataToShowPublicUserProfile_ByUsername(Request $request){
        
        //Check if username exists
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:users,username'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzername existiert nicht.'
            ]);
        }

        //check if user has a public publicUserProfile
        $userToShow = User::where('username', $request->username)->first();
        $publicUserProfile = $userToShow->public_user_profile;
        if($publicUserProfile == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat kein öffentliches Profil'
            ]);
        }
        if($publicUserProfile->public == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat sein Profil nicht veröffentlicht.'
            ]);
        }

        //If everything is fine
        $dataToReturn = new stdClass();

        //set publicUserProfile
        $dataToReturn->public_user_profile = $publicUserProfile;

        //set profilePicture base 64
        $dataToReturn->profile_picture_base64 = null;
        if($userToShow->profile_picture_name != null){
            $filename = $userToShow->profile_picture_name;
            if($filename != null){
                if(Storage::exists("/images/profilePictures/" . $filename)){
                    $image = Storage::get("/images/profilePictures/" . $filename);
                    $dataToReturn->profile_picture_base64 = base64_encode($image); 
                }
            }
        }

        //get latest co2calculation of current year of this user (with the given username)
        $climatemaster = $userToShow->climatemasters->where('year', Carbon::now()->year)->first();
        if($climatemaster != null){
            $co2Calculation = $climatemaster->co2calculations()->latest()->first();
        }

        //set climatemaster_status (climatemaster / climatemaster_starter / none)
        //climatemaster_starter -> if calculation->total_emissions <= 9.00 (9 Tonns CO2 eqivalent)
        $climatemaster_state = "none";
        if($climatemaster != null){
            if($climatemaster->verified == true){
                $climatemaster_state = "climatemaster";
            }
            //if not climatemaster but has co2 calculation
            else if($co2Calculation != null){
                if($co2Calculation->total_emissions <= 9.00){
                    $climatemaster_state = "climatemaster_starter";
                }
            }
        }
        $dataToReturn->climatemaster_state = $climatemaster_state;

        
        return response()->json([
            'public_user_profile' => $dataToReturn->public_user_profile,
            'climatemaster_state' => $dataToReturn->climatemaster_state,
            'profile_picture_base64' => $dataToReturn->profile_picture_base64,
            'state' => 'success',
            'message' => 'Die Daten wurden erfolgreich zusammengestellt und zurück gegeben.'
        ]);
        
    }
}
