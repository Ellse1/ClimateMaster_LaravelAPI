<?php

namespace App\Mail;

use App\EmailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class email_message_confirmation extends Mailable
{
    use Queueable, SerializesModels;

    protected $email_message;
    /**
     * Create a new message instance.
     *
     */
    public function __construct(EmailMessage $message)
    {
        $this->email_message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('Nachricht gesendet')
         ->view('mails.email_message_confirmation')
         ->with([
             'email_message' => $this->email_message
        ]);
    }
}
