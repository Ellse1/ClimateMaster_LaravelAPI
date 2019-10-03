<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    //Authentication
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:users,email|email',
            'password' => 'required'
        ]);

        $user = User::create([
             'email'    => $request->email,
             'password' => $request->password,
             'name' => $request->name,
         ]);


        if(!$token = auth()->attempt($request->only(['email', 'password']))){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

         
        return (new UserResource($user))
        ->additional([
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
                'errors' => [
                    'email' => ['There is something wrong! We could not verify details']
            ]], 422);
        }

        return (new UserResource($request->user()))
                ->additional([
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

        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}
