<?php

namespace App\Http\Controllers;

use App\Climatemaster;
use App\Climatemaster_steps_completed;
use App\CO2Calculation;
use App\Http\Resources\CO2CalculationResource;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class CO2CalculationController extends Controller
{

    public function __construct(){
        $this->middleware('auth.role:user,admin', ['except' => ['getLatestCO2CalculationForPublicProfileByUsername']]);
    }

    
    public function storeCO2Calculation_ByCurrentUser(Request $request){
        $validator = Validator::make($request->all(),[
            'link_uba_co2calculation' => 'required|string' //|unique:co2calculations,link_uba_co2calculation
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Dein Link ist leider nicht korrekt. ' . $validator->errors()
            ]);
        }
        
        //  Check if the link is a valid link to the uba co2 calculator (uba = umweltbundesamt = ministery for environment right)
        $result = Str::startsWith($request->link_uba_co2calculation, 'https://uba.co2-rechner.de/de_DE/?bookmark=');
        if($result == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Dein Link ist leider nicht korrekt und führt nicht zu einer CO2 Berechnung des Umweltbundesamtes '
            ]);
        }

        $user = User::find(auth()->user()->id);
        $climatemaster_steps_completed = $user->climatemaster_steps_completed->where('year', Carbon::now()->year)->first();
        
        //Now the person is allowed to change the calculation, even if he finished step3 (customize calculation) already
        //Check if in his year, climatemaster_steps_completed->customize_calculation is not already finished!   If finished, user is not allowed to change again!
        // if($climatemaster_steps_completed != null){
        //     if($climatemaster_steps_completed->customize_calculation == true){
        //         return response()->json([
        //             'state' => 'error',
        //             'message' => 'Der Schritt "Berechnung anpassen" wurde schon erfolgreich abgeschlossen. Für dieses Jahr, kann die Berechnung nicht mehr aktualisiert werden.'
        //         ]);
        //     }
        // }


        $ubaRequest = new Client();
        $ubaResponse = $ubaRequest->get($request->link_uba_co2calculation);
        $body = $ubaResponse->getBody();

        $data_splittet = explode('"dataProvider":[{', $body, 2)[1];
        $data_splittet_2 = explode('}', $data_splittet, 2)[0];
            
        $allData = explode('"category": "CO2-Ausstoß",', $data_splittet_2)[1];
        //allData looks like: 
        // "Öffentliche Emissionen": 0.73,
        // "sonstiger Konsum": 1.58,
        // "Ernährung": 1.68,
        // "Mobilität": 5.89,
        // "Heizung & Strom": 0.58  



        $allDataSplitted = explode(",", $allData);
        $onlyNumberData = [];
        $index = 0;
        foreach($allDataSplitted as $oneData){
            $onlyNumberData[$index] = explode(": ", $oneData)[1];
            $index ++;
        }

        // Creates new calculation
        $co2Calculation = new CO2Calculation();
        $co2Calculation->link_uba_co2calculation = $request->link_uba_co2calculation;

        $co2Calculation->public_emissions = round(floatval($onlyNumberData[0]), 2);
        $co2Calculation->consumption = round(floatval($onlyNumberData[1]), 2);
        $co2Calculation->nutrition = round(floatval($onlyNumberData[2]), 2);
        $co2Calculation->mobility = round(floatval($onlyNumberData[3]), 2);
        $co2Calculation->heating_electricity = round(floatval($onlyNumberData[4]), 2);
        $co2Calculation->total_emissions = $co2Calculation->public_emissions + $co2Calculation->consumption + $co2Calculation->nutrition + $co2Calculation->mobility + $co2Calculation->heating_electricity;

        //Look if user has a climateMaster for the current year
        $climatemaster = $user->climatemasters()->where('year', Carbon::now()->year)->first();

        if($climatemaster == null){
            //create new Climatemaster for this year
            $climatemaster = new Climatemaster();
            $climatemaster->user_id = $user->id;
            $climatemaster->year = Carbon::now()->year;
            $climatemaster->save();
        }


        $co2Calculation->climatemaster_id = $climatemaster->id;
        $co2Calculation->save();


        //Saves this step in climatemaster_steps_completed
        $climatemaster_steps_completed = $user->climatemaster_steps_completed()->where('year', Carbon::now()->year)->first();
        if($climatemaster_steps_completed == null){
            $climatemaster_steps_completed = new Climatemaster_steps_completed();
            $climatemaster_steps_completed->user_id = $user->id;
            $climatemaster_steps_completed->year = Carbon::now()->year;
        }
        $climatemaster_steps_completed->calculate = true;
        $climatemaster_steps_completed->save();

        return (new CO2CalculationResource($co2Calculation))->additional([
            'state' => 'success',
            'message' => 'Die Berechnung wurde erfolgreich gespeichert.'
        ]);

    }

    /**
     * Return latest Calculation of current user
     */
    public function getLatestCO2Calculation_ByCurrentUser(Request $request){
        $user = User::find(auth()->user()->id);
        $climatemaster = $user->climatemasters()->where('year', Carbon::now()->year)->first();

        if($climatemaster == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es gibt noch keine Berechnungen für das aktuelle Jahr.'
            ]);
        }

        $co2Calculation = $climatemaster->co2calculations()->latest()->first();

        if($co2Calculation == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es gibt noch keine CO2 Berechnung für dieses Jahr'
            ]);
        }

        //Check if user is climate master -> for co2 calculation chart -> if the person is climatemaster, show the kompensation too
        $userIsClimateMaster = $climatemaster->verified;

        return (new CO2CalculationResource($co2Calculation))->additional([
            'state' => 'success',
            'message' => 'Die Daten der CO2 Berechnung wurden erfolgreich zurück gegeben',
            'isCompensated' => $userIsClimateMaster
        ]);

    }

    /**
     * Get the latestCalculation if public profile is enabled by this user
     */
    public function getLatestCO2CalculationForPublicProfileByUsername(Request $request){
        
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:users,username'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde kein valider Benutzername angegeben.'
            ]);
        }
        
        $user = User::where('username', $request->username)->first();

        //check if user has a public profile and if public is enabled
        $publicProfile = $user->public_user_profile;
        if($publicProfile == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat kein öffentliches Profil.'
            ]);            
        }
        else if($publicProfile->public == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Dieser Benutzer hat sein Profil auf Privat geschalten.'
            ]);             
        }

        $climatemaster = $user->climatemasters()->where('year', Carbon::now()->year)->first();

        if($climatemaster == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es gibt noch keine Berechnungen für das aktuelle Jahr.'
            ]);
        }

        $co2Calculation = $climatemaster->co2calculations()->latest()->first();

        if($co2Calculation == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es gibt noch keine CO2 Berechnung für dieses Jahr'
            ]);
        }

        //Check if user is climate master -> for co2 calculation chart -> if the person is climatemaster, show the compensation too
        $userIsClimateMaster = $climatemaster->verified;


        return (new CO2CalculationResource($co2Calculation))->additional([
            'state' => 'success',
            'message' => 'Die Daten der CO2 Berechnung wurden erfolgreich zurück gegeben',
            'isCompensated' => $userIsClimateMaster
        ]);
    }

}
