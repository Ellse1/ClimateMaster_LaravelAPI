<?php

namespace App\Http\Controllers;

use App\Climatemasterpayment;
use App\Invoice;
use App\PayPalClient;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Srmklive\PayPal\Services\ExpressCheckout;

class PaypalController extends Controller
{


    /**
     * Checks a payment for a user -> creates in invoice and an payment for this year with the latest calculation in this year and saves it to db
     * checks if paid amout is big enoug
     * if big enoug -> sets steps_become_climatemaster->become_climatemaster = true + return success
     * if not big enough -> return error (with save payment and )
     */
    public function checkPayment(Request $request){
        
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine Order ID mitgegeben.'
            ]);
        }
        
        //Check if this user has done the step 1, 2, and 3 (calculate_emissions, reduce_short_term, customize_calculation)
        $user = User::find(auth()->user()->id);
        $steps_completed = $user->climatemaster_steps_completed->where('year', Carbon::now()->year)->first();
        if($steps_completed == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es müssen erst die ersten drei Schritte (CO2 Ausstoß berechnen, CO2 ausstoß veringern, CO2 Berechnung anpassen) gemacht werden.'
            ]);
        }
        if($steps_completed->calculate == false || $steps_completed->reduce_short_term == false || $steps_completed->customize_calculation == false || $steps_completed->become_climatemaster == true){
            return response()->json([
                'state' => 'error',
                'message' => 'Es müssen erst die ersten drei Schritte (CO2 Ausstoß berechnen, CO2 ausstoß veringern, CO2 Berechnung anpassen) gemacht werden. Der vierte Schritt (Climatemaster werden) darf noch nicht fertig sein.'
            ]);
        }

        //Get latest calculation of this user
        $climatemaster = $user->climatemasters()->where('year', Carbon::now()->year)->first();//Get latest climatemaster
        if($climatemaster == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es gibt noch keine Berechnungen für das aktuelle Jahr.'
            ]);
        }
        $co2Calculation = $climatemaster->co2calculations()->latest()->first();//Get latest co2 calculation of this climatemaster
        if($co2Calculation == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es gibt noch keine CO2 Berechnung für dieses Jahr'
            ]);
        }

        //Create payment 
        $climatemasterPayment = new Climatemasterpayment();
        $climatemasterPayment->user_id = $user->id;
        $climatemasterPayment->amount_to_pay = round($co2Calculation->total_emissions * 23.00, 2); //Two digits
        $climatemasterPayment->save();
        //create invoice
        $invoice = new Invoice();
        $invoice->climatemasterpayment_id = $climatemasterPayment->id;
        $invoice->save();

        //Gets the payment from paypal:
        $client = PayPalClient::client();
        $response = $client->execute(new OrdersGetRequest($request->order_id));

        if($response->result == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Die Daten von Paypal konnten nicht wie erwartet abgerufen werden. Wir würden uns sehr über eine Nachricht von Ihnen freuen!'
            ]);
        }

        $paypal_amount_paid = $response->result->purchase_units[0]->amount->value;
        $climatemasterPayment->amount_paid = $paypal_amount_paid;
        $climatemasterPayment->paid = true;
        $climatemasterPayment->save();

        //If paid not enough:
        if($climatemasterPayment->amount_paid < $climatemasterPayment->amount_to_pay){
            return response()->json([
                'state' => 'error',
                'message' => 'Der bezahlte Betrag ist kleiner als der zu zahlende Betrag. Es gab einen Fehler. Über eine Nachricht von Ihnen mit dieser Fehlernachricht würden wir uns sehr freuen.'
            ]);
        }

        //If paid enoug ->set steps completed:
        $steps_completed ->become_climatemaster = true;
        $steps_completed->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Es wurde erfolgreich Bezahlt.Vielen Dank für Ihre Bezahlung. Sie sind erfolgreich ClimateMaster geworden. ',
        ]);
    }
}
