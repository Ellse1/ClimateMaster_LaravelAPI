<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:admin,user', ['except' => ['getCompany', 'getCompanies', 'getActivatedCompaniesByClimadviceName']]);
    }


    /**
     * Return one Company by id
     */
    public function getCompany(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es muss ein Integer id mitgegeben werden.'
            ]);
        }
        $company = Company::find($request->id);
        if($company == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es konnte keine Firma gefunden werden'
            ]);
        }
        return (new CompanyResource($company))->additional([
            'state' => 'success',
            'message' => 'Firma wurde zurückgegeben.'
        ]);
    }

    /**
     * Gets all companies
     */
    public function getCompanies(){
        return (CompanyResource::collection(Company::all()))->additional([
            'state' => 'success',
            'message' => 'Es wurden alle Firmen zurückgegeben'
        ]);
    }

    /**
     * Return multiple companies depending on climadvice_name
     */
    public function getActivatedCompaniesByClimadviceName(Request $request){
        $validator = Validator::make($request->all(), [
            'climadvice_name' => 'required|exists:climadvices,name'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es muss ein verfügbarer climadvice_name mitgegeben werden. ' . $validator->errors()
            ]);
        }

        return (CompanyResource::collection(Company::where('climadvice_name', $request->climadvice_name)->where('verified', true)->get()))
                ->additional([
                    'state' => 'success',
                    'message' => 'Es wurden alle Firmen mit diesem climadvice_name zurück gegeben'
                ]);
    }



    /**
     * Stores the Company and attaches this to current user
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return error message (json) or company data with state=success
     */
    public function store(Request $request){
        
        $validator = Validator::make($request->all(),[
            'climadvice_name' => 'required|exists:climadvices,name', //Company belongs to a special climadvice
            'name' => 'required',
            'street' => 'required',
            'house_number' => 'required',
            'postcode' => 'required|integer',
            'residence' => 'required',
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Falsche Eingabe: ' . $validator->errors()
            ]);
        }

        $company = Company::create([
            'climadvice_name' => $request->climadvice_name,
            'name' => $request->name,
            'street' => $request->street,
            'house_number' => $request->house_number,
            'postcode' => $request->postcode,
            'residence' => $request->residence,
            'email' => $request->email
        ]);

        if($request->description != "undefined"){
            $company->description = $request->description;
        }

        $company->users()->attach(auth()->user());

        $company->save();

        return (new CompanyResource($company))
        ->additional([
            'state' => 'success',
            'message' => 'Firma erfolgreich gespeichert'
        ]);
    }



    /**
     * Updates the company data, if the current user has the right
     */
    public function update(Request $request){

        //Check if ok -> has id?
        $validator = Validator::make($request->all(),[
            'id' => 'required|integer|exists:companies,id',
            'climadvice_name' => 'required|exists:climadvices,name', //Company belongs to a special climadvice
            'name' => 'required',
            'street' => 'required',
            'house_number' => 'required',
            'postcode' => 'required|integer',
            'residence' => 'required',
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Falsche Eingabe: ' . $validator->errors()
            ]);
        }

        $company = Company::find($request->id);
        // check if current user i allowed to update this company
        $userID = auth()->user()->id;
        $user = $company->users()->find($userID);
        if($user == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie haben keine Berechtigung diese Firmendaten zu bearbeiten. Tut uns Leid.'
            ]);
        }


        $company->name = $request->name;
        $company->description = $request->description;
        $company->street = $request->street;
        $company->house_number = $request->house_number;
        $company->postcode = $request->postcode;
        $company->residence = $request->residence;
        $company->email = $request->email;

        $company->save();

        return (new CompanyResource($company))->additional([
            'state' => 'success',
            'message' => 'Die Firmendaten wurden erfolgreich geändert.'
        ]);

    }


    /**
     * Stores the banner for a company, if current user is allowed to
     */
    public function storeHeaderImage(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:companies,id',
            'header_image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Format oder größe stimmen nicht. ' . $validator->errors()
            ]);
        }

        $company = Company::find($request->id);
        // check if current user i allowed to update this company
        $userID = auth()->user()->id;
        $user = $company->users()->find($userID);
        if($user == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie haben keine Berechtigung diese Firmendaten zu bearbeiten. Tut uns Leid.'
            ]);
        }
        
        $imageName = "headerImage" . $company->id . "." . $request->header_image->getClientOriginalExtension();
        $imagePath = request()->header_image->move(public_path('images/companyImages/headerImages'), $imageName);

        $company->header_image_name = $imageName;
        $company->save();

        return (new CompanyResource($company))->additional([
            'state' => 'success',
            'message' => 'Firmenbanner wurde erfolgreich gespeichert.'
        ]);

    }


    /**
     * Stores the Logo of a company, if current user is allowed to
     */
    public function storeLogoImage(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:companies,id',
            'logo_image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Format oder größe stimmen nicht. ' . $validator->errors()
            ]);
        }

        $company = Company::find($request->id);
        // check if current user i allowed to update this company
        $userID = auth()->user()->id;
        $user = $company->users()->find($userID);
        if($user == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie haben keine Berechtigung diese Firmendaten zu bearbeiten. Tut uns Leid.'
            ]);
        }
        
        $imageName = "logoImage" . $company->id . "." . $request->logo_image->getClientOriginalExtension();
        $imagePath = request()->logo_image->move(public_path('images/companyImages/logoImages'), $imageName);

        $company->logo_image_name = $imageName;
        $company->save();

        return (new CompanyResource($company))->additional([
            'state' => 'success',
            'message' => 'Firmenlogo wurde erfolgreich gespeichert.'
        ]);

    }
}
