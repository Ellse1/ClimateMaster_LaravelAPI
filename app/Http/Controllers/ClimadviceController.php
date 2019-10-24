<?php

namespace App\Http\Controllers;

use App\Handlungsvorschlag;
use App\Http\Resources\ClimadviceResource;
use Illuminate\Http\Request;

class ClimadviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ClimadviceResource::collection(climadvices::all());
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
            'title' => 'required|unique:climadvices,titel',
            'shortDescription' => 'required|unique:climadvices,shortDescription|max:200',
            'detailedDescription' => 'required',
            'climadviceIcon' => 'required|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $imageName = $request->title . "." .$request->climadviceIcon->getClientOriginalExtension();
        
        $imagePath = request()->climadviceIcon->move(public_path('images/climadviceIcons'), $imageName);


        $climadvice = Climadvice::create([
            'title' => $request->title,
            'shortDescription' => $request->shortDescription,
            'detailedDescription' => $request->detailedDescription,
            'iconName' => $imageName
        ]);

        return (new ClimadviceResource($climadvice))
            ->additional([
                'state' => 'success',
                'message' => 'Climadvice erfolgreich erstellt'
            ]);

    }









    

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show($id)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit($id)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy($id)
    // {
    //     //
    // }
}
