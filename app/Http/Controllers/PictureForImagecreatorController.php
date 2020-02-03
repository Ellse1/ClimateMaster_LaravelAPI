<?php

namespace App\Http\Controllers;

use App\Http\Resources\PictureForImagecreatorResource;
use App\Picture_for_imagecreator;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class PictureForImagecreatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:user,admin');
    }


    /**
     * Stores an picture_for_imagecreator for the current user
     */
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'picture_for_imagecreator' => 'required|image|mimes:jpeg,jpg,png|max:2048'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Format oder größe stimmen nicht. ' . $validator->errors()
            ]);
        }

        //If not climatemaster -> not allowed to store a picture
        $this->checkIfUserIsClimatemaster();

        //check, if user has not already 6 pictures:
        $user = User::find(auth()->user()->id);
        
        $pictures_number_for_imagecreator = $user->pictures_for_imagecreator()->count();
        if($pictures_number_for_imagecreator >= 9){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie können höchstens 9 Bilder hochladen. Löschen Sie ein altes, damit Sie wieder ein neues hochladen können.'
            ]);
        }


        //Save this image (to get the id for the name)
        $picture_for_imagecreator = new Picture_for_imagecreator();
        $picture_for_imagecreator->user_id = $user->id;
        $picture_for_imagecreator->save();



        //defines the name for this picture
        $fileName = 'picture_for_imagecreator_' . $user->id . '_' . $picture_for_imagecreator->id . '.' . $request->file('picture_for_imagecreator')->getClientOriginalExtension();

        //stores the picture
        $path = $request->file('picture_for_imagecreator')->storeAs("images/pictures_for_imagecreator", $fileName);

        //saves the new picture_name (with user_id and picture id inside)
        $picture_for_imagecreator->picture_name = $fileName;
        $picture_for_imagecreator->save();

        //gets the picture
        // $image = Storage::get($path);

        //returns this picture with the base64 encoded picture (see PictureForImagecreatorResource for details)
        return (new PictureForImagecreatorResource($picture_for_imagecreator));

    }

    /**
     * Return all pictures of the current User
     */
    public function getPicturesOfCurrentUser(Request $request){

        //If not climatemaster -> don't get the climatemaster images
        $this->checkIfUserIsClimatemaster();
        
        $user = User::find(auth()->user()->id);
        $pictures_fromDB = $user->pictures_for_imagecreator()->get();

        // If he has not uploaded one yet -> look if he has a profile picture -> take this
        if(sizeof($pictures_fromDB) == 0 && $user->profile_picture_name != null){
            $profile_picture_name = $user->profile_picture_name;
            if(Storage::exists("/images/profilePictures/" . $profile_picture_name)){
                
                //Save this image (to get the id for the name)
                $picture_for_imagecreator = new Picture_for_imagecreator();
                $picture_for_imagecreator->user_id = $user->id;
                $picture_for_imagecreator->save();
                
                
                $profile_picture_extension = pathinfo(storage_path(). "/images/profilePictures/". $profile_picture_name, PATHINFO_EXTENSION);
                $fileName = 'picture_for_imagecreator_' . $user->id . '_' . $picture_for_imagecreator->id . '.' . $profile_picture_extension;
                // Copy the profilepicture to the picture_for_imagecreator   with right name
                Storage::copy("/images/profilePictures/" . $profile_picture_name, "images/pictures_for_imagecreator/" . $fileName);
                
                //save file name
                $picture_for_imagecreator->picture_name = $fileName;
                $picture_for_imagecreator->save();

                $user->save();

                //Get the pictures with the new one
                $pictures_fromDB = $user->pictures_for_imagecreator()->get();
            }
        }

        if($pictures_fromDB == null){
            return response()->json([
                'state' => 'error',
                'message' => 'es konnten leider keine Pictures gefunden werden'
            ]);
        }

        return (PictureForImagecreatorResource::collection($pictures_fromDB))->additional([
            'state' => 'success',
            'message' => 'Alle Pictures wurden erfolgreich zurückgegeben.'
        ]);
    }

    public function destroy(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:pictures_for_imagecreator,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine valide ID mit gegeben. ' . $validator->errors()
            ]);
        }

        $user = User::find(auth()->user()->id);
        $picture_for_imagecreator = $user->pictures_for_imagecreator->find($request->id);

        //The current user is not allowed to edit this picture
        if($picture_for_imagecreator == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie haben keine Berechtigung, dieses Bild zu bearbeiten'
            ]);
        }

        //if it finds the picture in storage
        if(Storage::exists("/images/pictures_for_imagecreator/" . $picture_for_imagecreator->picture_name)){
            unlink(storage_path("app/images/pictures_for_imagecreator/") . $picture_for_imagecreator->picture_name);
        }

        $picture_for_imagecreator->delete();

        return response()->json([
            'state' => 'success',
            'message' => 'Das Bild wurde erfolgreich gelöscht.'
        ]);

    }

    /**
     * Creates a nice Picture for the user and sends it back, so the user can download it
     */
    public function download(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => 'required|integer|exists:pictures_for_imagecreator,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'es wurde keine passende id mitgegeben'
            ]);
        }

        //check if user is climatemaster
        $this->checkIfUserIsClimatemaster();

        $user = User::find(auth()->user()->id);
        $ImageFromDB = $user->pictures_for_imagecreator()->find($request->id);

        if($ImageFromDB == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie haben keine Berechtigung, dieses Bild zu bearbeiten.'
            ]);
        }


        if(Storage::exists("/images/pictures_for_imagecreator/" . $ImageFromDB->picture_name) == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Das Bild wurde nicht gefunden.'
            ]);
        }
        $picture = Image::make(Storage::get("/images/pictures_for_imagecreator/" . $ImageFromDB->picture_name));

        // Logo
        $logo = Image::make(Storage::get("/files/for_image_creator/LogoTransparent.png"));
        $logo->resize(200, 200);
        $picture->insert($logo);

        
        //Set the right position for the climatemaster text
        $picture_width = $picture->width();
        $picture_height = $picture->height();

        // ClimateMaster Text -> Background
        $picture->rectangle(0, $picture_height-110, $picture_width, $picture_height-40, function ($draw) {
            $draw->background('rgba(247, 247, 247, 0.3)');
        });
        //CLimateMaster Text -> Text
        $picture->text('Climate', ($picture_width/2)-80, $picture_height-60, function($font) {
            $font->file(storage_path("app/files/for_image_creator/LiberationSerif-Bold.ttf"));
            $font->size(50);
            $font->color("#5cb85c");
            $font->align('right');
        });
        $picture->text('Master 2020', ($picture_width/2)-80, $picture_height-60, function($font) {
            $font->file(storage_path("app/files/for_image_creator/LiberationSerif-Bold.ttf"));
            $font->size(50);
            $font->align('left');
        });

        return response()->json([
            'state' => 'success',
            'message' => 'Das Bild wurde erfolgreich generiert.',
            'picture_base64' => base64_encode($picture->encode()->encoded)
        ]);

    }


    /**
     * 
     */
    public function updateSharingPermitted(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:pictures_for_imagecreator,id',
            'sharing_permitted' => 'required|boolean'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurden keine validen Daten mitgegeben: ' . $validator->errors()
            ]);
        }

        $this->checkIfUserIsClimatemaster();
        //check if it is his picture
        $user = User::find(auth()->user()->id);
        $picture = $user->pictures_for_imagecreator()->find($request->id);

        if($picture == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie haben keine Berechtigung dieses Bild zu veröffentlichen.'
            ]);
        }

        $picture->sharing_permitted = $request->sharing_permitted;
        $picture->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Die Berechtigung zur Veröffentlichung wurde erfolgreich gespeichert.'
        ]);
    }


    /**
     * Checks if the user is climatemaster of the current year -> if not, he is not allowed to use the imagecreator
     */
    private function checkIfUserIsClimatemaster(){
        $user = User::find(auth()->user()->id);
        $steps_completed_current_year = $user->climatemaster_steps_completed()
            ->where('year', Carbon::now()->year)
            ->where('become_climatemaster', true)
            ->first();

        if($steps_completed_current_year == null){
            abort(403, 'Sie sind nicht Climatemaster und haben deshalb keine Berechtigung diesen Service zu nutzen.');
        }
        else{
            return true;
        }
    }
}
