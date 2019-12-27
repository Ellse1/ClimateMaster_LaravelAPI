<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class registered extends Mailable
{
    use Queueable, SerializesModels;

    //Variables i need:
    protected $user, $verificationCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user,string $verificationCode)
    {
        $this->user = $user;
        $this->verificationCode = $verificationCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('Registrierung')
         ->view('mails.registered')
         ->with([
             'user' => $this->user,
             'verificationCode' =>  $this->verificationCode
         ]);
    }
}
