<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class verification extends Mailable
{
    use Queueable, SerializesModels;

    protected $user, $verificationCode;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $verificationCode)
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
        ->subject('Email Verifizierung')
        ->view('mails.verificationLink')
        ->with([
            'user' => $this->user,
            'verificationCode' => $this->verificationCode
        ]);
    }
}
