<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Http\Request;


interface AuthRepositoryInterface
{

    // Authentification
    public function login(string $email, string $password);

    // Inscription client
    public function registerClient(array $data);

    // Inscription intervenant
    public function registerIntervenant(array $data);

    // Déconnexion
    public function logout(User $user);

    // Récupérer l'utilisateur connecté avec ses rôles et permissions
    public function me(User $user);

    // Envoyer email de réinitialisation
    public function sendResetEmail(string $email);

    // Vérifier la validité du lien de réinitialisation (signed URL)
    public function verifyResetLink(Request $request);

    // Réinitialiser le mot de passe
    public function resetPassword(string $email, string $password);
}
