<?php
// app/Http/Controllers/API/AuthController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Client;
use App\Mail\ClientConfirmationMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\IntervenantConfirmationMail;




use App\Models\Intervenant;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('roles.permissions'),
            'token' => $token,
        ]);
    }

    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8|confirmed',
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     // Assign default role
    //     $user->assignRole('cliente');

    //     $token = $user->createToken('api-token')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Registration successful',
    //         'user' => $user->load('roles.permissions'),
    //         'token' => $token,
    //     ], 201);
    // }

    public function registerClient(Request $request)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'password' => [
        //         'required',
        //         'string',
        //         'min:8',
        //         'confirmed',
        //         'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
        //     ],
        //     'sexe' => 'required|in:Homme,Femme',
        //     'nom' => 'required|string|max:255',
        //     'prenom' => 'nullable|string|max:255',
        //     'nationalite' => ['required', 'regex:/^[A-Z]{2,3}$/'],
        //     'adresse' => 'required|string|max:255',
        //     'indicatif' => 'nullable|string|max:10',
        //     'telephone' => ['nullable', 'regex:/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/'],
        //     'conditions' => 'accepted',
        // ]);

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
        Mail::to($user->email)->send(new ClientConfirmationMail($user));


        return response()->json([
            'message' => 'Inscription client réussie',
            'user' => $user->load('roles.permissions', 'cliente'),
            'token' => $token,
        ], 201);
    }

    public function registerIntervenant(Request $request)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'password' => [
        //         'required',
        //         'string',
        //         'min:8',
        //         'confirmed',
        //         'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
        //     ],
        //     'type_entreprise' => 'required|in:Auto-Entrepreneur,Freelancer,Entreprise',
        //     'nom_entreprise' => 'required_if:type_entreprise,Entreprise|string|max:255',
        //     'activite_entreprise' => 'nullable|string|max:255',
        //     'categorie_activite' => 'nullable|string|max:255',
        //     'ville' => 'required|string|max:255',
        //     'adresse' => 'required|string|max:255',
        //     'telephone' => ['nullable', 'regex:/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/'],
        //     'conditions' => 'accepted',
        // ]);

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
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles.permissions')
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Mot de passe réinitialisé avec succès.'])
            : response()->json(['message' => 'Erreur lors de la réinitialisation.'], 500);
    }




    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Email de réinitialisation envoyé avec succès.'])
            : response()->json(['message' => 'Erreur lors de l\'envoi de l\'email.'], 500);
    }
}
