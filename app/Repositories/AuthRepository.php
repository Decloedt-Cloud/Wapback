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
                'email' => ['The provided credentials are incorrect.'],
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
                'user' => $user->load('roles.permissions'),
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
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole('intervenant');

        Intervenant::create([
            'user_id' => $user->id,
            'type_entreprise' => $request->type_entreprise,
            'nom_entreprise' => $request->nom_entreprise,
            'activite_entreprise' => $request->activite_entreprise,
            'categorie_activite' => $request->categorie_activite,
            'ville' => $request->ville,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;
        Mail::to($user->email)->send(new IntervenantConfirmationMail($user));


        return response()->json([
            'message' => 'Inscription intervenant réussie',
            'user' => $user->load('roles.permissions', 'intervenant'),
        ], 201);
    }



    public function sendResetEmail($request)
    {
        $user = User::where('email', operator: $request->email)->firstOrFail();

        // Générer l’URL temporaire signée Laravel backend
        $signedUrl = URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(60),
            ['email' => $user->email]
        );

        $queryString = parse_url($signedUrl, PHP_URL_QUERY);

        $resetUrl = 'http://preprod.hellowap.com/ /Resetpassword?' . $queryString;

        Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));

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
            'password' => 'required|string|min:8|confirmed', // requires password_confirmation
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
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            Mail::to($user->email)->send(mailable: new ClientConfirmationMail($user));

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
