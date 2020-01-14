<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:user,admin');
    }


    /**
     * Add profilePicture to user
     */
    public function addProfilePicture(Request $request){
        
        $validator = Validator::make($request->all(), [
            'profilePicture' => 'required|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        $user = auth()->user();
        $userID = $user ->id;
        $fileName = 'profilePicture' . $userID . '.' . $request->file('profilePicture')->getClientOriginalExtension();

        $path = $request->file('profilePicture')->storeAs("images/profilePictures", $fileName);

        $user->profile_picture_name = $fileName;
        $user->save();

        // error_log($fileName);

        $image = Storage::get($path);

        // return response()->file(storage_path('app/' . $path), ['Content-Type' , 'Image']);
        return base64_encode($image);

    }

    public function getProfilePicture(Request $request){
        $user = auth()->user();
        $filename = $user->profile_picture_name;
        if($filename != null){
            if(Storage::exists("/images/profilePictures/" . $filename)){
                $image = Storage::get("/images/profilePictures/" . $filename);
                return base64_encode($image);
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
}
