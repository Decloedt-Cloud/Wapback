<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Client;
use App\Models\Intervenant;
use App\Mail\ClientConfirmationMail;
use App\Mail\IntervenantConfirmationMail;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AuthRepository implements AuthRepositoryInterface
{
    public function login($request)
    {


        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Votre adresse email n\'a pas encore été vérifiée. Veuillez vérifier votre boîte mail.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Connexion réussie.',
            'data' => [
                'user' => $user->load('roles', 'intervenant', 'cliente'),
                'token' => $token,
            ]
        ];
    }

    public function registerClient($request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('cliente');

        Client::create([
            'user_id' => $user->id,
            'sexe' => $request->sexe,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'nationalite' => $request->nationalite,
            'adresse' => $request->adresse,
            'indicatif' => $request->indicatif,
            'telephone' => $request->telephone,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;
        Mail::to($user->email)->send(mailable: new ClientConfirmationMail($user));

        return response()->json([
            'message' => 'Inscription client réussie',
            'user' => $user->load('roles.permissions', 'cliente'),
        ], 201);
    }
    public function registerIntervenant($request)
    {
        $user = User::create([
            'email' => $request->email,
            'name' => '',
            'password' => Hash::make($request->password),
        ]);


        $user->assignRole('intervenant');

        Intervenant::create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;
        Mail::to($user->email)->send(new IntervenantConfirmationMail($user));


        return response()->json([
            'message' => 'Inscription intervenant réussie',
            'user' => $user->load('roles.permissions', 'intervenant'),
            'token' => $token,

        ], 201);
    }



    public function sendResetEmail($request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email'    => 'Veuillez saisir une adresse e-mail valide.',
            'email.exists'   => 'Cette adresse e-mail n’existe pas dans notre base de données.',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // 🔹 Lien temporaire signé valable 2 minutes
        $signedUrl = URL::temporarySignedRoute(
            'password.reset',      // route backend
            now()->addMinutes(2),  // expiration 2 minutes
            ['email' => $user->email]
        );

        // 🔹 On envoie ce lien complet dans l'email
        Mail::to($user->email)->send(new PasswordResetMail($user, $signedUrl));

        return response()->json(['message' => 'Email de réinitialisation envoyé']);
    }


    public function verifyResetLink($request)
    {
        if (! $request->hasValidSignature()) {
            return response()->json(['message' => 'Lien de réinitialisation invalide ou expiré.'], 401);
        }

        return response()->json([
            'message' => 'Lien valide, vous pouvez réinitialiser votre mot de passe.',
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword($request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse e-mail valide.',
            'email.exists' => 'Aucun compte n’est associé à cette adresse e-mail.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = User::where('email', $request->email)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }

    public function resendConfirmation($request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse e-mail valide.',
            'email.exists' => 'Aucun compte n’est associé à cette adresse e-mail.',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if ($user && $user->hasRole('Intervenant')) {
                Mail::to($user->email)->send(mailable: new IntervenantConfirmationMail($user));
            } else {
                Mail::to($user->email)->send(mailable: new ClientConfirmationMail($user));
            }

            return response()->json([
                'message' => 'Email de confirmation renvoyé avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de l’envoi de l’email.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
