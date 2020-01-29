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
        $this->middleware('auth.role:user,admin');
    }

    
    public function store(Request $request){
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
        $user = User::find(auth()->user()->id);
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

    public function getLatestCalculation(Request $request){
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

        return (new CO2CalculationResource($co2Calculation))->additional([
            'state' => 'success',
            'message' => 'Die Daten der CO2 Berechnung wurden erfolgreich zurück gegeben'
        ]);

    }

}
