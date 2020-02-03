<?php

namespace App\Http\Controllers;

use App\Company;
use App\User;
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

    public function saveAddress(Request $request){
        $user = auth()->user();

        if($request->street != "undefined"){
            $user->street = $request->street;
        }

        if($request->house_number != "undefined"){
            $user->house_number = $request->house_number;
        }
        
        if($request->postcode != "undefined"){
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
        
        $user->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Adressdaten erfolgreich gespeichert.'
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
}
