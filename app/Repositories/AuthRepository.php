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

class AuthRepository implements AuthRepositoryInterface
{
    public function login(string $email, string $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ["Votre adresse email n'a pas encore été vérifiée."],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user->load('roles.permissions'),
            'token' => $token,
        ];
    }

    public function registerClient(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('cliente');

        Client::create([
            'user_id' => $user->id,
            'sexe' => $data['sexe'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'] ?? null,
            'nationalite' => $data['nationalite'],
            'adresse' => $data['adresse'],
            'indicatif' => $data['indicatif'] ?? null,
            'telephone' => $data['telephone'] ?? null,
        ]);

        Mail::to($user->email)->send(new ClientConfirmationMail($user));

        return $user;
    }

    public function registerIntervenant(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('intervenant');

        Intervenant::create([
            'user_id' => $user->id,
            'type_entreprise' => $data['type_entreprise'],
            'nom_entreprise' => $data['nom_entreprise'] ?? null,
            'activite_entreprise' => $data['activite_entreprise'] ?? null,
            'categorie_activite' => $data['categorie_activite'] ?? null,
            'ville' => $data['ville'],
            'adresse' => $data['adresse'],
            'telephone' => $data['telephone'] ?? null,
        ]);

        Mail::to($user->email)->send(new IntervenantConfirmationMail($user));

        return $user;
    }

    public function logout(User $user)
    {
        $user->currentAccessToken()->delete();
    }

    public function me(User $user): User
    {
        return $user->load('roles.permissions');
    }

    public function sendResetEmail(string $email)
    {
        $user = User::where('email', $email)->firstOrFail();

        $signedUrl = URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(60),
            ['email' => $user->email]
        );

        $queryString = parse_url($signedUrl, PHP_URL_QUERY);

        $resetUrl = 'http://preprod.hellowap.com/Resetpassword?' . $queryString;

        Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));
    }

    public function verifyResetLink(Request $request)
    {
        return $request->hasValidSignature();
    }

    public function resetPassword(string $email, string $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user) return false;

        $user->password = Hash::make($password);
        return $user->save();
    }
}
