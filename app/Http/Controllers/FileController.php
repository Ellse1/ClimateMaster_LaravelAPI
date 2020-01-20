<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __construct(){
        $this->middleware('auth.role:admin', ['except' => ['showConceptSummary']]);
    }

    public function showConceptSummary(){
        // $conceptSummary = Storage::get("/files/Konzept Zusammenfassung.pdf");
        return response()->file(storage_path("app/files/conceptSummary.pdf"));
    }

    // public function showConcept(){
    //     return response()->file(storage_path("app/files/concept.pdf"));
    // }

}
