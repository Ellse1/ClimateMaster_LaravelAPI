<?php

namespace App\Http\Controllers;

use App\EmailMessage;
use App\Mail\email_message;
use App\Mail\email_message_confirmation;
use App\Mail\email_message_to_admin;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailMessageController extends Controller
{

    
    /*
        sends and stores a message
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'message' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'state' => 'error',
                'message' => $validator->errors()
            ]);
        }

        $email_message = new EmailMessage();
        $email_message->email = $request->email;
        $email_message->message = $request->message;
        $email_message->save();
        
        // Mail to sender
        Mail::to($request->email)->send(new email_message_confirmation($email_message));
        
        // // Mail to me
        Mail::to("elias.singer@online.de")->send(new email_message_to_admin($email_message));

        return response()->json([
            'state' => 'success',
            'message' => 'Die Nachricht wurde erfolgreich gesendet. 
            Wir werden uns so schnell wie möglich zurück melden. 
            Außerdem haben wir dir eine Empfangsbestätigung an die von dir angegebene E-Mail geschickt.
             Bis bald. TIPP: Noch schneller erreichst du uns über WhatsApp und Telegram.'
        ]);

    }



}
