<?php

namespace App\Http\Controllers;

use App\Climatemasterpayment;
use App\Invoice;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Srmklive\PayPal\Services\ExpressCheckout;

class PaypalController extends Controller
{


    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request){

        $validator = Validator::make($request->all(),[
            'roundup' => 'required|boolean',
            'id' => 'required|integer|exists:users,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Tut uns Leid, für die Bezahlung fehlen Parameter: ' . $validator->errors()
            ]);
        }
        
        //Check if this user has done the step 1, 2, and 3 (calculate_emissions, reduce_short_term, customize_calculation)
        $user = User::find($request->id);
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

        //Get latest calculation
        $user = User::find($request->id);
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

        $quantity = 0.00;
        $totalPrice = 0;
        $pricePerTonnCO2 = 23.00; //€
        $tonnsToCompensate = $co2Calculation->total_emissions;

        if($request->roundup == true){
            $quantity = ceil($co2Calculation->total_emissions);
            $totalPrice = $quantity * $pricePerTonnCO2; //Two digits after the comma
            $tonnsToCompensate = $quantity;
        }else{
            //Quantity has to be integer!
            $quantity = 1;
            $totalPrice = $co2Calculation->total_emissions * $pricePerTonnCO2; //two digits after comma
            $pricePerTonnCO2 = $totalPrice;
        }

        $float_totalPrice = floatval(number_format($totalPrice, 2)); //two digits after comma

        error_log("qty : " . $quantity);
        error_log("price per tonn: " . $pricePerTonnCO2);
        error_log("price total: " . $float_totalPrice);

        $data = [];
        $data['items'] = [
            [
                'name' => 'ClimateMaster CO2 Kompensierung: ' . $tonnsToCompensate . " Tonnen",
                'price' => $pricePerTonnCO2,
                'desc'  => 'CO2 Kompensierung',
                'qty' => $quantity
            ]
        ];


        //Create invoice and payment
        $payment = new Climatemasterpayment();
        $payment->user_id = $request->id;
        $payment->amount_to_pay = $totalPrice;
        $payment->save();
        
        $invoice = new Invoice();
        $invoice->climatemasterpayment_id = $payment->id;
        $invoice->save();


        $data['invoice_id'] = $invoice->id;
        $data['invoice_description'] = "Order #{$invoice->id} Invoice";
        $data['return_url'] = route('paypal.success');
        $data['cancel_url'] = route('paypal.cancel');
        $data['total'] = $float_totalPrice;

        $provider = new ExpressCheckout();

        $response = $provider->setExpressCheckout($data);

        $response = $provider->setExpressCheckout($data, true);

        return $response;

        return redirect($response['paypal_link']);
    
    }
   
    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        //return to error page
        return redirect('https://www.climate-master.com/error_pages/paypal_climatemasterpayment_canceled');
    }
  
    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {        
        //ADDED BY MYSELF, if not -> profider is undefined
        $provider = new ExpressCheckout();

        $response = $provider->getExpressCheckoutDetails($request->token);
  
        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            
            //get climatemasterpayment and set 'paid' true
            $invoiceID = intval($response["INVNUM"]);
            $invoice = Invoice::find($invoiceID);
            $payment = $invoice->climatemasterpayment;
            $payment->amount_paid = floatval($response["AMT"]);
            $payment->paid = true;
            $payment->save();


            //check if the payment amount is surely big enough to become climatemaster for the year of payment
            if( $payment->amount_paid < $payment->amount_to_pay){
                return redirect('https://www.climate-master.com/myClimateMaster')->with([
                    'state' => 'error',
                    'message' => 'Der bezahlte Betrag war kleiner als der zu zahlende Betrag. Etwas ist schief gelaufen, bitte melden Sie sich direkt persönliche bei uns'
                ]);
            }


            //Set climatemaster_steps_completed -> become_climatemaster = true
            $user = $payment->user;
            //The year the payment was created -> get the latest calculation of the climatemaster of this year
            $yearOfPayment = $payment->created_at->year;
            $steps_completed = $user->climatemaster_steps_completed->where('year', $yearOfPayment)->first();
            $steps_completed->become_climatemaster = true;
            $steps_completed->save();



            return redirect('https://www.climate-master.com/myClimateMaster?paid=true')->with([
                'state' => 'success',
                'message' => 'Erfolgreich zum ClimateMaster dieses Jahres aufgestiegen!'
            ]);
            // return $response;
        }
  
        dd('Something is wrong.');
    }
}
