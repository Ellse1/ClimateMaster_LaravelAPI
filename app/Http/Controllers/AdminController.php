<?php

namespace App\Http\Controllers;

use App\Climatemaster;
use App\Company;
use App\Http\Resources\CO2CalculationResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Mail\congratulationBecomeClimatemaster;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
    public function activateCompany(Request $request){
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
    public function deactivateCompany(Request $request){
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


    /*show all users*/
    public function getAllUsers(Request $request){
        return (UserResource::collection(User::all()))->additional([
            'state' => 'success', 
            'message' => 'Es wurde erfolgreich alle User zurückgegeben.'
        ]);
    }
    /**
     * Set one user as climatemaster for this year (If someone paid not with paypal, but direct to ISBN or direct to atmosfair)
     * Send gratulation link to this person
     */
    public function setUserClimatemaster(Request $request){
        
        $validator = Validator::make($request->all(),[
            'id' => 'required|integer|exists:users,id' //User to set as 'climatemaster' => true
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser User konnte nicht gefunden werden'
            ]);
        }

        //get User
        $userToMakeClimatemaster = User::find($request->id);

        $steps_completed = $userToMakeClimatemaster->climatemaster_steps_completed()->where('year', Carbon::now()->year)->first();

        //If anything is wrong
        if(!($steps_completed->calculate && $steps_completed->reduce_short_term && $steps_completed->customize_calculation && $steps_completed->become_climatemaster == false)){
            return response()->json([
                'state' => 'error',
                'message' => 'This User did not all the neccesary Steps to become Climatemaster or this Person is already Climatemaster!'
            ]);
        }

        //If everything is as expected (the user did the first 3 steps but not the step "become_climatemaster"
        $steps_completed->become_climatemaster = true;
        $steps_completed->save();

        //send Mail to the user -> congratulation
        Mail::to($userToMakeClimatemaster->email)->send(new congratulationBecomeClimatemaster($userToMakeClimatemaster));

        return response()->json([
            'state' => 'success',
            'message' => 'Der User wurde erfolgreich zum ClimateMaster für dieses Jahr gemacht. Außerdem wurde Ihm eine Mail mit dem Gratulationslink geschickt.'
        ]);
    }

    /**
     * Get the latest calculaton of a user -> to make sure if he paid enough to become climatemaster
     */
    public function getLastCalculationOfUser(Request $request){
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


}
