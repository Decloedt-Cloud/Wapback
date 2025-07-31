<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Http\Request;


interface AuthRepositoryInterface
{

    // Authentification
    public function login($request);

    // Inscription client
    public function registerClient($request);

    // Inscription intervenant
    public function registerIntervenant($request);

    // Déconnexion

    // Récupérer l'utilisateur connecté avec ses rôles et permissions

    // Envoyer email de réinitialisation
    public function sendResetEmail($request);

    // Vérifier la validité du lien de réinitialisation (signed URL)
    public function verifyResetLink($request);

    // Réinitialiser le mot de passe
    public function resetPassword($request);
}
