<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;

        // Génère le lien temporaire signé, valable 2 minutes
        $this->resetUrl = URL::temporarySignedRoute(
            'password.reset', // cette route doit exister
            Carbon::now()->addMinutes(2),
            ['email' => $this->user->email]
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Réinitialisation de votre mot de passe')
                    ->view('emails.password_reset')
                    ->with([
                        'user' => $this->user,
                        'resetUrl' => $this->resetUrl,
                    ]);
    }
}
