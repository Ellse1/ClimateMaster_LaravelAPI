<?php

namespace App\Http\Controllers;

use App\Http\Resources\PageLogResource;
use App\PageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageLogController extends Controller
{
    //

    public function addPageLog(Request $request){
        $validator = Validator::make($request->all(),[
            'page' => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine Seite (page) mitgegeben' . $validator->errors()
            ]);
        }


        $pageLog = new PageLog();
        $pageLog->page = $request->page;
        $pageLog->parameter = $request->parameter;
        $pageLog->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Erfolgreicher PageLog'
        ]);

    }

    public function getPageLogs_ByPageAndParameter(Request $request){
        $validator = Validator::make($request->all(), [
            'page' => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Es wurde keine Seite (page) mitgegeben, von der die Logs zurückgegeben werden sollen.'
            ]);
        }

        $pageLog = null;

        if($request->parameter != null){
            $pageLogs = PageLog::where('page', $request->page)
                ->where('parameter', $request->parameter)
                ->get();
        }
        else{
            $pageLogs = PageLog::where('page', $request->page)->get();
        }


        return (PageLogResource::collection($pageLogs))->additional([
            'state' => 'success',
            'message' => 'Es wurden alle PageLogs dieser Seite zurückgegeben.'
        ]);
    }
}
