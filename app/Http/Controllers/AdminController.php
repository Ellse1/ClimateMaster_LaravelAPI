<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:admin');
    }

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



}
