<?php

namespace App\Http\Controllers;

use App\Climadvice;
use App\Http\Resources\ClimadviceResource;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

//toTestMail
use Illuminate\Support\Facades\Mail;


class ClimadviceController extends Controller
{

    //Constructor:
    public function __construct()
    {
        $this->middleware('auth.role:admin', ['except' => ['index']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ClimadviceResource::collection(climadvice::all());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Check if user is logged in
        if(Auth::check() == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Du bist nicht eingeloggt'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:climadvices,name',
            'title' => 'required|unique:climadvices,title',
            'shortDescription' => 'required|unique:climadvices,shortDescription|max:200',
            'iconName' => 'required',
            'easy' => 'required',
            'climateMasterArea' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        
        $climadvice = Climadvice::create([
            'name' => $request->name,
            'title' => $request->title,
            'shortDescription' => $request->shortDescription,
            'iconName' => $request->iconName,
            'easy' => (int)$request->easy,
            'climateMasterArea' => $request->climateMasterArea
        ]);

        return (new ClimadviceResource($climadvice))
            ->additional([
                'state' => 'success',
                'message' => 'Climadvice erfolgreich erstellt'
            ]);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //Check if user is logged in
        if(Auth::check() == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Du bist nicht eingeloggt'
            ]);
        }

        //validate
        $validator = Validator::make($request->all(), [
            'id' => "required",
            'name' => "required",
            'title' => "required",
            'shortDescription' => "required",
            'iconName' => "required",
            'easy' => "required",
            'climateMasterArea' => "required"
        ]);


        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $climadvice = Climadvice::find($request->id);
        $climadvice->title = $request->title;
        $climadvice->shortDescription = $request->shortDescription;
        $climadvice->iconName = $request->iconName;
        $climadvice->easy = (int)$request->easy;
        $climadvice->climateMasterArea = $request->climateMasterArea;
        $climadvice->save();

        return (new ClimadviceResource($climadvice))
            ->additional([
                'state' => 'success',
                'message' => 'Climadvice erfolgreich geändert'
            ]);

    }


     /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(Auth::check() == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Du bist nicht eingeloggt'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        //Remove Image
        $climadvice = Climadvice::find($request->id);

        $deleted =  $climadvice->forceDelete();

        return response()->json([
            'state' => 'success',
            'message' => 'Climadvice erfolgreich gelöscht'
        ]);
    }

}
