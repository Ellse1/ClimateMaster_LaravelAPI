<?php

namespace App\Http\Controllers;

use App\ClimadviceCheck;
use App\Climatemaster;
use App\Company;
use App\Http\Resources\ClimadviceCheckResource;
use App\Http\Resources\CO2CalculationResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\PageLogResource;
use App\Http\Resources\PictureForImagecreatorResource;
use App\Http\Resources\UserResource;
use App\Mail\congratulationBecomeClimatemaster;
use App\PageLog;
use App\Picture_for_imagecreator;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:admin');
    }

    /*To activate a company*/
    public function getCompaniesToActivate(){
        return (CompanyResource::collection(Company::where('verified', false)->get()))
            ->additional([
                'state' => 'success',
                'message' => 'Es wurde alle Firmen zur verifizierung zurückgegeben.'
            ]);
    }
    public function activateCompany_ByCompanyID(Request $request){
        $validator = Validator::make($request->all(),[
            'company_id' => 'required|integer|exists:companies,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine valide Firmen ID mitgegeben. ' . $validator->errors()
            ]);
        }

        $company = Company::find($request->company_id);
        $company->verified = true;
        $company->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Die Firma wurde erfolgreich aktiviert.'
        ]);
    }
    public function deactivateCompany_ByCompanyID(Request $request){
        $validator = Validator::make($request->all(),[
            'company_id' => 'required|integer|exists:companies,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine valide Firmen ID mitgegeben. ' . $validator->errors()
            ]);
        }

        $company = Company::find($request->company_id);
        $company->verified = false;
        $company->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Die Firma wurde erfolgreich deaktiviert.'
        ]);
    }


    /*return all users*/
    public function getAllUsers(Request $request){
        return (UserResource::collection(User::all()))->additional([
            'state' => 'success', 
            'message' => 'Es wurde erfolgreich alle User zurückgegeben.'
        ]);
    }
        
    /**
     * Get the latest calculaton of a user -> to make sure if he paid enough to become climatemaster
     */
    public function getLastCO2CalculationOfUser_ByUserID(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'success',
                'message' => 'Es wurde keine gültige ID mitgegeben.'
            ]);
        }

        //Get climatemaster of this user, of current year
        $climatemaster = Climatemaster::where('user_id', $request->user_id)->where('year', Carbon::now())->first();
        if($climatemaster == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es gibt noch keine Berechnungen für das aktuelle Jahr.'
            ]);
        }
        //Get the latest co2 calculation
        $co2calculation = $climatemaster->co2calculations()->latest()->first();
        
        if($co2calculation == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es gibt noch keine CO2 Berechnung für dieses Jahr'
            ]);
        }

        return (new CO2CalculationResource($co2calculation))->additional([
            'state' => 'success',
            'message' => 'Die Daten der CO2 Berechnung wurden erfolgreich zurück gegeben'
        ]);
    }

    /**
     * Set one user as climatemaster for this year (If someone paid not with paypal, but direct to ISBN or direct to atmosfair)
     * Send gratulation link to this person
     */
    public function setUserClimatemaster_ByUserID(Request $request){
        
        $validator = Validator::make($request->all(),[
            'user_id' => 'required|integer|exists:users,id' //User to set as 'climatemaster' => true
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser User konnte nicht gefunden werden'
            ]);
        }

        //get User
        $userToMakeClimatemaster = User::find($request->user_id);

        $steps_completed = $userToMakeClimatemaster->climatemaster_steps_completed()->where('year', Carbon::now()->year)->first();

        //If anything is wrong
        if(!($steps_completed->calculate && $steps_completed->reduce_short_term && $steps_completed->customize_calculation && $steps_completed->become_climatemaster == false)){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat nicht alle nötigen Schritte abgeschlossen bzw. ist schon ClimateMaster geworden.'
            ]);
        }

        //If everything is as expected (the user did the first 3 steps but not the step "become_climatemaster"
        $steps_completed->become_climatemaster = true;
        $steps_completed->save();

        //Save in the 'climatemaster' model of this year, that process is now completed and verified
        $climatemaster = $userToMakeClimatemaster->climatemasters()->where('year', Carbon::now())->first();
        $climatemaster->process_completed = true;
        $climatemaster->verified = true;
        $climatemaster->date_climatemaster_verified = Carbon::now();
        $climatemaster->save();

        //send Mail to the user -> congratulation
        Mail::to($userToMakeClimatemaster->email)->send(new congratulationBecomeClimatemaster($userToMakeClimatemaster));

        return response()->json([
            'state' => 'success',
            'message' => 'Der User wurde erfolgreich zum ClimateMaster für dieses Jahr gemacht. Außerdem wurde Ihm eine Mail mit dem Gratulationslink geschickt.'
        ]);
    }




    /**
     * Get all Images for Imagecreator -> all images for imagecreator with "public" = true
     */
    public function getAllImagesForPublication(Request $request){
        $imagesForImagecreator = Picture_for_imagecreator::where('sharing_permitted', true)->get();
        return (PictureForImagecreatorResource::collection($imagesForImagecreator))->additional([
            'state' => 'success',
            'message' => 'Es wurden alle Bilder zurückgegeben, bei denen Teilen erlaubt ist.'
        ]);
    }
    /**
     * Get the image to download
     */
    public function downloadPictureFromImagecreator_ByPictureForImagecreatorID(Request $request){
        $validator = Validator::make($request->all(), [
            'picture_for_imagecreator_id' => 'required|integer|exists:pictures_for_imagecreator,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurden nicht die richtigen Paramenter mit gegeben. picture_for_imagecreator_id existiert nicht oder hat das falsche Format.' 
            ]);
        }


        $ImageFromDB = Picture_for_imagecreator::find($request->picture_for_imagecreator_id);

        if($ImageFromDB->sharing_permitted != true){
            return response()->json([
                'state' => 'error',
                'message' => 'Das Teilen dieses Bildes ist nicht erlaubt. sharing_permitted != true!' 
            ]);
        }
        
        if(Storage::exists("/images/pictures_for_imagecreator/" . $ImageFromDB->picture_name) == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Das Bild wurde nicht gefunden.'
            ]);
        }
        $picture = Image::make(Storage::get("/images/pictures_for_imagecreator/" . $ImageFromDB->picture_name));
        //resize the picture to fixed hight of 1000px (auto width)
        $picture->resize(null, 1000, function ($constraint) {
            $constraint->aspectRatio();
        });
        // Logo
        $logo = Image::make(Storage::get("/files/for_image_creator/LogoTransparent.png"));
        $logo->resize(200, 200);
        $picture->insert($logo);

        
        //Set the right position for the climatemaster text
        $picture_width = $picture->width();
        $picture_height = $picture->height();

        // ClimateMaster Text -> Background
        $picture->rectangle(0, $picture_height-140, $picture_width, $picture_height-40, function ($draw) {
            $draw->background('rgba(247, 247, 247, 0.3)');
        });
        //CLimateMaster Text -> Text
        $picture->text('Climate', ($picture_width/2)-80, $picture_height-90, function($font) {
            $font->file(storage_path("app/files/for_image_creator/LiberationSerif-Bold.ttf"));
            $font->size(50);
            $font->color("#5cb85c");
            $font->align('right');
        });
        $picture->text('Master 2020', ($picture_width/2)-80, $picture_height-90, function($font) {
            $font->file(storage_path("app/files/for_image_creator/LiberationSerif-Bold.ttf"));
            $font->size(50);
            $font->align('left');
        });
        //Umwerltfreundlich klimaneutral Text
        $picture->text('Umweltfreundlich klimaneutral', ($picture_width/2)-80, $picture_height-60, function($font) {
            $font->file(storage_path("app/files/for_image_creator/LiberationSerif-Bold.ttf"));
            $font->size(20);
            $font->align('center');
        });

        return response()->json([
            'state' => 'success',
            'message' => 'Das Bild wurde erfolgreich generiert.',
            'picture_base64' => base64_encode($picture->encode()->encoded)
        ]);



    }


    /**
     * Return all page logs -> to show in admin dashboard
     */
    public function getAllPageLogs(Request $request){
        
        return (PageLogResource::collection(PageLog::all()))->additional([
            'state' => 'success',
            'message' => 'Es wurden alle PageLogs zurückgegeben.'
        ]);
        
    }



    /**
     * Save a climadviceCheck
     */
    public function saveClimadviceCheck(Request $request){
        $validator = Validator::make($request->all(), [
            'climadvice_id' => 'required|integer|exists:climadvices,id',
            'action' => 'required',
            'question' => 'required',
            'answer_proposal' => 'required',
            'button_send_text' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurden nicht alle nötigen Paramenter mitgegeben um den ClimadviceCheck zu speichern. ' + $validator->errors() 
            ]);
        }

        $climadviceCheck = new ClimadviceCheck();
        $climadviceCheck->climadvice_id = $request->climadvice_id;
        $climadviceCheck->action = $request->action;
        $climadviceCheck->question = $request->question;
        $climadviceCheck->answer_proposal = $request->answer_proposal;
        $climadviceCheck->button_send_text = $request->button_send_text;
        $climadviceCheck->save();

        return (new ClimadviceCheckResource($climadviceCheck))->additional([
            'state' => 'success',
            'message' => 'Der ClimadviceCheck wurde erfolgreich gespeichert'
        ]);

    }

    /**
     * Delete ClimadviceCheck by ID
     */
    public function deleteClimadviceCheck_ByClimadviceCheckID(Request $request){
        $validator = Validator::make($request->all(), [
            'climadviceCheckID' => 'required|integer|exists:climadvice_checks,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurden nicht die richtigen Paramenter mitgegeben. ' + $validator->errors()
            ]);
        }

        $climadviceCheck = ClimadviceCheck::find($request->climadviceCheckID);
        $climadviceCheck->delete();

        return response()->json([
            'state' => 'success',
            'message' => 'Der ClimadviceCheck wurde erfolgreich gelöscht.'
        ]);
    }
}
