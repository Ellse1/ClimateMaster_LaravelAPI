<?php

namespace App\Http\Controllers;

use App\Company;
use App\CompanySlideshowimage;
use App\Http\Resources\CompanySlideshowimageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\File\File;

class CompanySlideshowimageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:user,admin', ['except' => ['getSlideshowimages_ByCompanyID']]);
    }

    /**
     * Return the slideshowimages (From DB) for the company
     */
    public function getSlideshowimages_ByCompanyID(Request $request){
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
    public function storeSlideshowimage(Request $request){
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


    /**
     * Destroy a slideshowimage by id
     */
    public function destroySlideshowimage_BySlideshowimageID(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:company_slideshowimages,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine valide id angegeben: ' . $validator->errors()
            ]);
        }

        $companyslideshowimage = CompanySlideshowimage::find($request->id);

        //Check if user is admin of company
        $userID = auth()->user()->id;
        $company = $companyslideshowimage->company;
        $user = $company->users()->find($userID);

        if($user == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Sie haben nicht die Berechtigung, diesen Bild zu löschen'
            ]);
        }

        $imagePath = public_path('images/companyImages/slideshowimages/') .  $companyslideshowimage->image_name;

        if(file_exists($imagePath)){
            unlink($imagePath);
        }
        else{
            return response()->json([
                'state' => 'error',
                'message' => 'Das image konnte nicht gefunden werden.'
            ]);
        }
        
        $companyslideshowimage->delete();

        return response()->json([
            'state' => 'success',
            'message' => 'Das CompanySlideshowimage wurde erfolgreich gelöscht.'
        ]);
    }
}
