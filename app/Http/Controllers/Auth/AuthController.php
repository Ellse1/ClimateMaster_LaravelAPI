<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Mail\password_reset;
use App\Mail\registered;
use App\Mail\verification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    //Authentication
    public function register(Request $request)
    {

        $messages = [
            'username.unique' => "Der Benutzername ':input' ist schon vergeben. Waehle einen anderen Benutzernamen"
        ];

        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|unique:users,email|email',
            'password' => 'required|min:8'
        ], $messages);


        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Registrierung fehlgeschlagen: ' . $validator->errors()
            ]);
        }

        // Create user with role: 'user'
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password,
            'role' => 'user',
            'street' => $request->street,
            'house_number' => $request->house_number,
            'postcode' => $request->postcode,
            'residence' => $request->residence
         ]);


        if(!$token = auth()->attempt($request->only(['email', 'password']))){
            return response()->json([
                'state' => 'error',
                'message' => 'Nicht autorisiert'], 401
            );
        }

        //Send verification Mail
        $verificationCode = Str::random(22);
        $hashedCode = bcrypt($verificationCode);
        $user->email_verification_code = $hashedCode;
        $user->save();
        
        Mail::to($user->email)->send(new registered($user, $verificationCode));

         
        return (new UserResource($user))
        ->additional([
            'state' => 'success',
            'message' =>    'Registrierung erfolgreich. 
                            Wir haben dir eine Email mit dem Aktivierungslink gesendet.
                            Öffne deine Emails um dein ClimateMaster Konto zu aktivieren.
                            Falls du keine Email bekommen hast, kannst du einen neuen Aktivierungslink anfordern.',
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

        //Check for right password and email
        if(!$token = auth()->attempt($request->only(['email', 'password'])))
        {
            return response()->json([
                'state' => 'error',
                'message' => 'Falsche Email oder Passwort'], 401);
        }

        //Check if email is verified
        $user = User::where('email', $request->email)->first();
        if($user->email_verified_at == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Ihre Email wurde noch nicht verifiziert. 
                Wir haben Ihnen eine Email mit einem Link zur Verifizierung gesendet. 
                Folgen Sie diesem Link, um Ihr Konto zu aktivieren.'], 401);
            
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
        return (new UserResource($request->user()))
        ->additional([
            'state' => 'success',
            'message' => 'Der aktuell angemeldete User wurde zurückgegeben.'
        ]);
    }


    public function logout()
    {
        // Set last login
        $userID = auth()->user()->id;

        $user = User::find($userID);
        $user->last_login =  Carbon::now();
        $user->save();

        auth()->logout();

        return response()->json([
            'state' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    //Verificates a user email after register
    public function verification(Request $request){

        $validator = Validator::make($request->all(), [
            'userID' => 'required|integer',
            'verificationCode' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Verifizierung fehlgeschlagen: ' . $validator->errors()
            ]);
        }

        $user = User::find($request->userID);


        //If unable to find this user
        if($user == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Verifizierung fehlgeschlagen. User konnte nicht gefunden werden.'
            ]);
        }

        //if user_email is already verified:
        if($user->email_verified_at != null){
            return response()->json([
                'state' => 'success',
                'message' => 'Email ist schon verifiziert worden. Sie können sich direkt einloggen.'
            ]);
        } 


        //If the verificatioCode is not right
        if(!Hash::check($request->verificationCode, $user->email_verification_code)){
            return response()->json([
                'state' => 'error',
                'message' => 'Verifizierung fehlgeschlagen. Falscher Verifizierungscode.'
            ]);
        }
        //VerificationCode is right
        else{
            $user->email_verified_at = Carbon::now();
            $user->email_verification_code = "";
            $user->save();
            return response()->json([
                'state' => 'success',
                'message' => 'Verifizierung erfolgreich. Sie können sich jetzt einloggen.'
            ]);
        }
    }

    //Send the verification link again
    public function resendVerificationLink(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Verifizierungslink konnte nicht gesendet werden. ' . $validator->errors()
            ]);
        }

        //Check if email exists
        $user = User::where('email', $request->email)->first();
        
        if($user == null){
            return response()->json([
                'state' => 'error', 
                'message' => 'Tut uns leid. Kein Konto gefunden.'
            ]);
        }
        //If already verified:
        if($user->email_verified_at != null){
            return response()->json([
                'state' => 'success',
                'message' => 'Diese Email wurde schon verifiziert. Sie können sich einfach einloggen und loslegen.'
            ]);
        }

        //If not verified:
        //Send verification Mail
        $verificationCode = Str::random(22);
        $hashedCode = bcrypt($verificationCode);
        $user->email_verification_code = $hashedCode;
        $user->save();
        
        Mail::to($user->email)->send(new verification($user, $verificationCode));

        return response()->json([
            'state' => 'success',
            'message' => 'Wir haben Ihnen erneut einen Verifizierungslink an Ihre Email Adresse gesendet. 
            Folgen Sie diesem Link, um Ihr ClimateMaster Konto zu aktivieren.'
        ]);

    }

    //Sends the PasswordResetLink
    public function sendPasswordResetLink(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Konnte den ResetLink nicht verschicken.' . $validator->errors()
            ]);
        }

        $user = User::where('email', $request->email)->first();
        //If user does't exist:
        if($user == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Tut uns leid. Leider kein Konto gefunden'
            ]);
        }

        //Generate password reset code and send to user
        $user->password_reset = true;
        $password_reset_code = STR::random(22);
        $user->password_reset_code = bcrypt($password_reset_code);
        $user->save();
        Mail::to($user->email)->send(new password_reset($user, $password_reset_code));

        return response()->json([
            'state' => 'success',
            'message' => 'Wir haben Ihnen einen Email mit einem Link gesendet. 
            Folgen Sie diesem Link, um Ihr Passwort zurückzusetzen.'
        ]);
    }

    //Sets the new Password:
    public function setNewPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'userID' => 'required|integer',
            'password_reset_code' => 'required',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => 'Passwort zurücksetzen fehlgeschlagen. ' . $validator->errors()
            ]);
        }

        //check if user available and reset_password == true
        $user = User::find($request->userID);

        if($user == null){
            return response()->json([
                'state' => 'error',
                'message' => 'Tut uns leid. Kein Konto für den Passwortreset gefunden.'
            ]);
        }
        else if(!$user->password_reset){
            return response()->json([
                'state' => 'error',
                'message' => 'Tut uns leid. Kein Konto für den Passwortreset gefunden.'
            ]);
        }

        //if verification code is not right:
        if(!Hash::check($request->password_reset_code, $user->password_reset_code)){
            return response()->json([
                'state' => 'error',
                'message' => 'Tut uns leid. Wir konnten dein Passwort nicht ändern, weil der Code nicht gültig war.
                 Versuche über "Passwort vergessen" einen neuen Code anzufordern.'
            ]);
        }

        $user->password = $request->password; //bcrypt in User Model
        $user->password_reset = false;
        $user->password_reset_code = null;
        $user->save();

        return response()->json([
            'state' => 'success',
            'message' => 'Passwort wurde erfolgreich geändert. Sie können sich jetzt einloggen.'
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
