<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class ClientConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }


    public function build()
    {
        // Génère le lien de vérification signé, valable 60 minutes
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', // Cette route doit exister dans api.php
            Carbon::now()->addMinutes(60),
            [
                'id' => $this->user->id,
                'hash' => sha1($this->user->email),
            ]
        );

        return $this->subject('Confirmation de votre inscription (Client)')
            ->view('emails.confirmation_client')
            ->with([
                'user' => $this->user,
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
