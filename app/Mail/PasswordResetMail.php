<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->resetUrl = url('/reset-password/' . $token);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reset Your Password')
                    ->view('emails.password-reset')
                    ->with([
                        'token' => $this->token,
                        'resetUrl' => $this->resetUrl,
                    ]);
    }
}
