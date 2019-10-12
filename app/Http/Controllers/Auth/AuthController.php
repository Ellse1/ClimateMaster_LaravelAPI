<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //Authentication
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Registrierung fehlgeschlagen',
                'errors' => $validator->errors()
            ]);
        }


        $user = User::create([
             'email'    => $request->email,
             'password' => $request->password,
             'name' => $request->name,
         ]);


        if(!$token = auth()->attempt($request->only(['email', 'password']))){
            return response()->json([
                'state' => 'error',
                'message' => 'Nicht autorisiert'], 401
            );
        }

         
        return (new UserResource($user))
        ->additional([
            'state' => 'success',
            'meta' => [
                'token' => $token
            ]
        ]);

        // $token = auth()->login($user);

        // return $this->respondWithToken($token);
    }

    public function login(Request $request)
    { 
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        if(!$token = auth()->attempt($request->only(['email', 'password'])))
        {
            return response()->json([
                'state' => 'error',
                'message' => 'Falsche Email oder Passwort'], 401);
        }

        return (new UserResource($request->user()))
                ->additional([
                    'state' => 'success',
                    'meta' => [
                        'token' => $token
                    ]
                ]);
    }

    public function user(Request $request){
        return new UserResource($request->user());
    }





    public function logout()
    {
        auth()->logout();

        return response()->json([
            'state' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    // protected function respondWithToken($token)
    // {
    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type'   => 'bearer',
    //         'expires_in'   => auth()->factory()->getTTL() * 60
    //     ]);
    // }
}
