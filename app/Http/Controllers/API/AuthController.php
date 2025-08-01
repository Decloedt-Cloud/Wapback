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
                'email' => ['The provided credentials are incorrect.'],
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
            'user' => $user->load('roles.permissions'),
            'token' => $token,
        ]);
    }


    public function registerClient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
            'sexe' => 'required|in:Homme,Femme',
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'nationalite' => ['required', 'regex:/^[A-Z]{2,3}$/'],
            'adresse' => 'required|string|max:255',
            'indicatif' => 'nullable|string|max:10',
            'telephone' => ['nullable', 'regex:/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/'],
            'conditions' => 'accepted',
        ]);

        return $this->authRepository->registerClient($request);
    }

    public function registerIntervenant(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
            'type_entreprise' => 'required|in:Auto-Entrepreneur,Freelancer,Entreprise',
            'nom_entreprise' => 'required_if:type_entreprise,Entreprise|string|max:255',
            'activite_entreprise' => 'nullable|string|max:255',
            'categorie_activite' => 'nullable|string|max:255',
            'ville' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'telephone' => ['nullable', 'regex:/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/'],
            'conditions' => 'accepted',
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

    public function resendConfirmation(Request $request){
        return $this->authRepository->resendConfirmation($request);
    }
}
