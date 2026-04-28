<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $verificationUrl;
    public $userName;

    /**
     * Create a new message instance.
     */
    public function __construct($token, $userName = null)
    {
        $this->token = $token;
        $this->verificationUrl = url('/verify-email/' . $token);
        $this->userName = $userName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Verify Your Email Address')
                    ->view('emails.email-verification')
                    ->with([
                        'token' => $this->token,
                        'verificationUrl' => $this->verificationUrl,
                        'userName' => $this->userName,
                    ]);
    }
}
