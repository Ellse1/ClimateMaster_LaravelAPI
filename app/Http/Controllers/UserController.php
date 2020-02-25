<?php

namespace App\Http\Controllers;

use App\Company;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:user,admin', ['except' => ['isCompanyAdmin']]);
    }


    /**
     * Add profilePicture to user
     */
    public function addProfilePicture(Request $request){
        
        $validator = Validator::make($request->all(), [
            'profilePicture' => 'required|image|mimes:jpeg,jpg,png|max:2048'
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

    public function getProfilePicture(Request $request){
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

    public function saveAddressAndInstagram(Request $request){
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
        
        $user->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Adressdaten bzw. Instagram Name erfolgreich gespeichert.'
        ]);
    }



    /**
     * Looks if the current user is Admin for a given company
     * 
     * @params -> company_id
     */
    public function isCompanyAdmin(Request $request){
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
    public function checkShowGratulationBecomingClimateMaster(Request $request){
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
        $last_login = $user->last_login;

        //If he was verified as climatemaster, but didn't login since that -> show_gratulation!
        //last_login lessThan (date_climatemaster_verified)
        if($user->last_logout->lt($climatemaster->date_climatemaster_verified)){
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
}
