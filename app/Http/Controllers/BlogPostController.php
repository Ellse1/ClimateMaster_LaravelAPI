<?php

namespace App\Http\Controllers;

use App\BlogPost;
use App\Http\Resources\BlogPostResource;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Integer;

class BlogPostController extends Controller
{

    //Constructor:
    public function __construct()
    {
        $this->middleware('auth.role:admin', ['except' => ['index', 'getBlogPost']]);
    }


    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return (BlogPostResource::collection(BlogPost::all()->sortByDesc('created_at')))
            ->additional([
                'state' => 'success',
                'message' => 'Erfolgreich alles BlogPosts zurückgegeben.'
            ]);
    }


    //Get only one blogPost
    public function getBlogPost(Request $request){

        $blogPost = BlogPost::find($request->id);
        
        if($blogPost == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Es konnte kein BlogPost gefunden werden'
            ]);
        }
        return (new BlogPostResource($blogPost))->additional([
            'state' => 'success',
            'message' => 'Einen BlogPost zurück gegeben.'
        ]);
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
            'heading' => 'required',
            'previewContent' => 'required',
            'postContent' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }



        $blogPost = BlogPost::create([
            'heading' => $request->heading,
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


        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'heading' => 'required',
            'previewContent' => 'required',
            'postContent' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $blogPost = BlogPost::find($request->id);
        $blogPost->heading = $request->heading;
        $blogPost->previewContent = $request->previewContent;
        $blogPost->postContent = $request->postContent;

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

            //If there is already a image for this blogPost -> Delete it:
            $imageName = "blogPostImage" . $blogPost->id . "." . $request->postImage->getClientOriginalExtension();
            if(File::exists(public_path("/images/BlogPostImages/" . $imageName))){
                 File::delete(public_path("/images/BlogPostImages/" . $imageName));
             }

            $imagePath = request()->postImage->move(public_path('images/BlogPostImages'), $imageName);

            $blogPost->imageName = $imageName;
        }

        $blogPost->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Der Blogpost wurde erfolgreich geändert.'
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
        //Check if user is logged in
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

        //Gets the blogPost
        $blogPost = BlogPost::find($request->id);

        //If Image exists -> remove
        if($blogPost->imageName != null){
            $imagePath = public_path("/images/BlogPostImages/" . $blogPost->imageName);
            File::delete($imagePath);
        }

        $deleted = $blogPost->forceDelete();

        return response()->json([
            'state' => 'success',
            'message' => 'BlogPost erfolgreich gelöscht'
        ]);
    }

}
