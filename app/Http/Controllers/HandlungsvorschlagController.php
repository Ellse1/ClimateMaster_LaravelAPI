<?php

namespace App\Http\Controllers;


use App\Handlungsvorschlag;
use App\Http\Resources\HandlungsvorschlagResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class HandlungsvorschlagController extends Controller
{

    //Store a new Handlungsmoeglichkeit
    public function store(Request $request){

        //Check if user is logged in
        if(Auth::check() == false){
            return response()->json([
                'state' => 'error',
                'message' => 'Du bist nicht eingeloggt'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:handlungsvorschlag,titel',
            'kurzbeschreibung' => 'required|unique:handlungsvorschlag,kurzbeschreibung|max:200',
            'detailbeschreibung' => 'required',
            'climadviceIcon' => 'required|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $imageName = $request->titel . "." .$request->vorschlagIcon->getClientOriginalExtension();
        
        $imagePath = request()->vorschlagIcon->move(public_path('images/vorschlaegeIcons'), $imageName);

        $handlungsvorschlag = Handlungsvorschlag::create([
            'title' => $request->titel,
            'kurzbeschreibung' => $request->kurzbeschreibung,
            'detailbeschreibung' => $request->detailbeschreibung,
            'iconName' => $imageName
        ]);

        return (new HandlungsvorschlagResource($handlungsvorschlag))
            ->additional([
                'state' => 'success',
                'message' => 'Handlungsvorschlag erfolgreich erstellt'
            ]);

    }
    
}
