<?php

namespace App\Http\Controllers;

use App\Company;
use App\CompanySlideshowimage;
use App\Http\Resources\CompanySlideshowimageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanySlideshowimageController extends Controller
{
    public function __construct()
    {
        
    }


    /**
     * Return the slideshowimages (From DB) for the company
     */
    public function getSlideshowimageByCompanyID(Request $request){
        $validator = Validator::make($request->all(),[
            'company_id' => "required|integer|exists:companies,id"
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es muss eine id für die Firma übergeben werden (company id)'
            ]);
        }

        return (CompanySlideshowimageResource::collection(CompanySlideshowimage::where('company_id', $request->company_id)->get()))
            ->additional([
                'state' => 'success',
                'message' => 'Alle slideshowimages der Firma zurückgegeben.'
            ]);
    }


    /**
     * Stores a slideshowimage for a company, if current user is allowed to edit this company
     */
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer|exists:companies,id',
            'slideshowimage' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'caption' => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Format oder größe stimmen nicht. ' . $validator->errors()
            ]);
        }

        $company = Company::find($request->company_id);
        // check if current user i allowed to update this company
        $userID = auth()->user()->id;
        $user = $company->users()->find($userID);
        if($user == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie haben keine Berechtigung diese Firmendaten zu bearbeiten. Tut uns Leid.'
            ]);
        }
        
        $companyslideshowimage = CompanySlideshowimage::create([
            'company_id' => $request->company_id,
            'caption' => $request->caption
        ]);


        $imageName = "companyslideshowimage" . $companyslideshowimage->id . "_" . $company->id . "." . $request->slideshowimage->getClientOriginalExtension();
        $imagePath = request()->slideshowimage->move(public_path('images/companyImages/slideshowimages'), $imageName);

        $companyslideshowimage->image_name = $imageName;
        $companyslideshowimage->save();

        return (new CompanySlideshowimageResource($companyslideshowimage))->additional([
            'state' => 'success',
            'message' => 'Slideshowimage wurde erfolgreich gespeichert.'
        ]);

    }
}
