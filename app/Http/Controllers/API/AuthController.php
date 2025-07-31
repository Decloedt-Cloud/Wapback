<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $this->authRepository->login($request->email, $request->password);

        if (!$result['success']) {
            throw ValidationException::withMessages([
                'email' => [$result['message']],
            ]);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $result['user'],
            'token' => $result['token'],
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

        $user = $this->authRepository->registerClient($request->all());

        return response()->json([
            'message' => 'Inscription client réussie',
            'user' => $user->load('roles.permissions', 'cliente'),
            'token' => $user->createToken('api-token')->plainTextToken,
        ], 201);
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

        $user = $this->authRepository->registerIntervenant($request->all());

        return response()->json([
            'message' => 'Inscription intervenant réussie',
            'user' => $user->load('roles.permissions', 'intervenant'),
            'token' => $user->createToken('api-token')->plainTextToken,
        ], 201);
    }

    public function logout(Request $request)
    {
        $this->authRepository->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        $user = $this->authRepository->me($request->user());

        return response()->json([
            'user' => $user->load('roles.permissions'),
        ]);
    }

    public function sendResetEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $this->authRepository->sendResetEmail($request->email);

        return response()->json(['message' => 'Email de réinitialisation envoyé']);
    }

    public function verifyResetLink(Request $request)
    {
        if (!$this->authRepository->verifyResetLink($request)) {
            return response()->json(['message' => 'Lien de réinitialisation invalide ou expiré.'], 401);
        }

        return response()->json([
            'message' => 'Lien valide, vous pouvez réinitialiser votre mot de passe.',
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $success = $this->authRepository->resetPassword($request->email, $request->password);

        if (!$success) {
            return response()->json(['message' => 'Échec de la réinitialisation du mot de passe.'], 500);
        }

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}
