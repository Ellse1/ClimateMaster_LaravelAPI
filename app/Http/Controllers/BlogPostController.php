<?php

namespace App\Http\Controllers;

use App\BlogPost;
use App\Http\Resources\BlogPostResource;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;


class BlogPostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return BlogPostResource::collection(BlogPost::all());
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
            'previewContent' => 'required|',
            'postContent' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }



        $blogPost = BlogPost::create([
            'previewContent' => $request->previewContent,
            'postContent' => $request->postContent
        ]);
        
        //If I send a Image with this Post
        if($request->postImage != "undefined"){
            $validator= Validator::make($request->all(), [
                'postImage' => 'required|image|mimes:jpeg,jpg,png|max:2048',//not required
            ]);
            if($validator->fails()){
                return response()->json([
                    'state' => 'error',
                    'message' => $validator->errors()
                ]);
            }

            $imageName = "blogPostImage" . $blogPost->id . "." . $request->postImage->getClientOriginalExtension();
            $imagePath = request()->postImage->move(public_path('images/BlogPostImages'), $imageName);

            $blogPost->imageName = $imageName;
            $blogPost->save();
        }
        
        return (new BlogPostResource($blogPost))
            ->additional([
                'state' => 'success',
                'message' => 'BlogPost erfolgreich erstellt'
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
            'shortDescription' => "required"
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
        $imagePath = public_path('images/climadviceIcons/') . $climadvice->iconName;
        File::delete($imagePath);

        $deleted =  $climadvice->forceDelete();

        return response()->json([
            'state' => 'success',
            'message' => 'Climadvice erfolgreich gelöscht'
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
