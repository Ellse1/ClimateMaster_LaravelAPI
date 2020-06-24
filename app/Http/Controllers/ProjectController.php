<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.role:admin', ['except' => ['getAll'], ['get_ByName']]);
    }



    /**
     * Create / Store a project
     */
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'short_description' => 'required',
            'description' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde kein valides Projekt mitgegeben. ' . $validator->errors()
            ]);
        }

        $project = new Project();
        $project->name = $request->name;
        $project->title = $request->title;
        $project->short_description = $request->short_description;
        $project->description = $request->description;
        $project->save();

        return (new ProjectResource($project))->additional([
            'state' => 'success',
            'message' => 'Das Projekt wurde erfolgreich gespeichert.'
        ]);
    }  


    /**
     * Returns all Projects
     */
    public function getAll(){
        $projects = Project::all();

        return (ProjectResource::collection($projects))->additional([
            'state' => 'success',
            'message' => 'Es wurden alle Projekte erfolgreich zurück gegeben.'
        ]);
    }

    /**
     * Returns a Project -> to edit
     */
    public function get_ByID(Request $request){
        
        // validation
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:projects,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine valide ID mitgegeben.' . $validator->errors()
            ]);
        }

        return (new ProjectResource(Project::find($request->id)))->additional([
            'state' => 'success', 
            'message' => 'Das Projekt wurde erfolgreich zurückgegeben'
        ]);


    }

        /**
     * Returns the Project by Name
     */
    public function get_ByName(Request $request){
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|exists:projects,name'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Das Projekt wurde nicht gefunden.'
            ]);
        }

        $project = Project::where('name', $request->name)->first();

        return (new ProjectResource($project))->additional([
            'state' => 'success',
            'message' => 'Das Projekt wurde erfolgreich zurück gegeben.'
        ]);
    }


    /**
     * Saves the changed project
     */
    public function edit_ByID(Request $request){
        
        // validation
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:projects,id',
            'name' => 'required',
            'short_description' => 'required',
            'description' => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde kein valides Projekt mitgegeben.' . $validator->errors()
            ]);
        }


        $project = Project::find($request->id);
        $project->name = $request->name;
        $project->title = $request->title;
        $project->short_description = $request->short_description;
        $project->description = $request->description;
        $project->save();




        return (new ProjectResource($project))->additional([
            'state' => 'success', 
            'message' => 'Das Projekt wurde erfolgreich gespeichert.'
        ]);


    }

}
