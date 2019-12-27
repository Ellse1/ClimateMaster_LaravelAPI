<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class password_reset extends Mailable
{
    use Queueable, SerializesModels;

    protected $user, $password_reset_code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $password_reset_code)
    {
        $this->user = $user;
        $this->password_reset_code = $password_reset_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('Passwort zurücksetzen')
        ->view('mails.passwordResetCode')
        ->with([
            'user' => $this->user,
            'password_reset_code' => $this->password_reset_code
        ]);
    }
}
