<?php
// app/Http/Controllers/API/AuthController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Client;
use Illuminate\Support\Facades\Password;
use App\Models\Intervenant;
use App\Mail\ClientConfirmationMail;
use App\Mail\IntervenantConfirmationMail;
use App\Mail\PasswordResetMail;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;




class AuthController extends Controller
{

    protected $authRepository;

    public function __construct(AuthRepositoryInterface $AuthRepository)
    {
        $this->authRepository = $AuthRepository;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        // ✅ Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Votre adresse email n\'a pas encore été vérifiée. Veuillez vérifier votre boîte mail.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' =>     $user->load('roles.permissions', 'vendor'),
            'token' => $token,
        ]);
    }


    public function registerClientStep1(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
                ],
            ]);

            $user = User::create([
                'email' => $request->email,
                'name' => '',
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole('cliente');

            Client::create(['user_id' => $user->id]);

            $token = $user->createToken('api-token')->plainTextToken;

            Mail::to($user->email)->send(new ClientConfirmationMail($user));

            return response()->json([
                'message' => 'Préinscription réussie. Vérifiez votre email pour continuer.',
                'token' => $token,
                'user_id' => $user->id
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'enregistrement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function completeClientProfile(Request $request)
    {
        try {
            $request->validate([
                'sexe' => 'required|in:Homme,Femme',
                'prenom' => 'required|regex:/^[A-Za-zÀ-ÿ\s\-]+$/',
                'nom' => 'required|regex:/^[A-Za-zÀ-ÿ\s\-]+$/',
                'adresse' => 'required|string|max:255',
                'nationalite' => ['required', 'regex:/^[A-Z]{2,3}$/'],
                'indicatif' => 'nullable|string|max:10',
                'telephone' => ['nullable', 'regex:/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/'],
                'date_naissance_jour' => 'required|integer|min:1|max:31',
                'date_naissance_mois' => 'required|integer|min:1|max:12',
                'date_naissance_annee' => 'required|integer|min:1900|max:' . now()->year,
                'langue_maternelle' => 'required|string|max:100',
                'lieu_naissance' => 'required|regex:/^[A-Za-zÀ-ÿ\s\-]+$/',
            ]);

            $user = $request->user();

            if (! $user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email non vérifié.'], 403);
            }

            $user->name = $request->prenom . ' ' . $request->nom;


            $client = Client::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'sexe' => $request->sexe,
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'adresse' => $request->adresse,
                    'nationalite' => $request->nationalite,
                    'indicatif' => $request->indicatif,
                    'telephone' => $request->telephone,
                    'date_naissance' => "{$request->date_naissance_annee}-{$request->date_naissance_mois}-{$request->date_naissance_jour}",
                    'langue_maternelle' => $request->langue_maternelle,
                    'lieu_naissance' => $request->lieu_naissance,
                    'profil_rempli' => true,
                ]
            );
            $user->profil_rempli = true;
            $user->save();

            return response()->json([
                'message' => 'Profil client complété avec succès.',
                'client' => $client,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la complétion du profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // public function registerClient(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => [
    //             'required',
    //             'string',
    //             'min:8',
    //             'confirmed',
    //             'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
    //         ],
    //         'sexe' => 'required|in:Homme,Femme',
    //         'nom' => 'required|string|max:255',
    //         'prenom' => 'nullable|string|max:255',
    //         'nationalite' => ['required', 'regex:/^[A-Z]{2,3}$/'],
    //         'adresse' => 'required|string|max:255',
    //         'indicatif' => 'nullable|string|max:10',
    //         'telephone' => ['nullable', 'regex:/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/'],
    //         'conditions' => 'accepted',
    //     ]);

    //     return $this->authRepository->registerClient($request);
    // }

    public function registerIntervenant(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
            // 'type_entreprise' => 'required|in:Auto-Entrepreneur,Freelancer,Entreprise',
            // 'nom_entreprise' => 'required_if:type_entreprise,Entreprise|string|max:255',
            // 'activite_entreprise' => 'nullable|string|max:255',
            // 'categorie_activite' => 'nullable|string|max:255',
            // 'ville' => 'required|string|max:255',
            // 'adresse' => 'required|string|max:255',
            // 'telephone' => ['nullable', 'regex:/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/'],
        ]);


        return $this->authRepository->registerIntervenant($request);
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


    public function sendResetEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        return $this->authRepository->sendResetEmail($request);
    }


    // 2. Vérifier la validité du lien signé
    public function verifyResetLink(Request $request)
    {
        return $this->authRepository->verifyResetLink($request);
    }

    public function resetPassword(Request $request)
    {
        return $this->authRepository->resetPassword($request);
    }

    public function resendConfirmation(Request $request)
    {
        return $this->authRepository->resendConfirmation($request);
    }

    public function resendConfirmationWeb(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        Mail::to($user->email)->send(new IntervenantConfirmationMail($user));

        return redirect()->back()->with('status', '✅ Nouveau lien de vérification envoyé !');
    }
}
