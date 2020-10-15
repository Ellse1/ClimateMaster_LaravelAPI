<?php

namespace App\Mail;

use App\EmailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class email_message_to_admin extends Mailable
{
    use Queueable, SerializesModels;

    protected $email_message;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(EmailMessage $email_message)
    {
        $this->email_message = $email_message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('Nachricht bekommen')
         ->view('mails.email_message_to_admin')
         ->with([
             'email_message' => $this->email_message
        ]);
    }
}
