<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategorieController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\IntervenantController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\VendorController;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use App\Models\User;


// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/register-intervenant', [AuthController::class, 'registerIntervenant']);
Route::post('/register-client-step1', [AuthController::class, 'registerClientStep1']);

Route::post('/forgot-password', [AuthController::class, 'sendResetEmail']);
Route::get('/reset-password', [AuthController::class, 'verifyResetLink'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/resend-confirmation', [AuthController::class, 'resendConfirmation']);


Route::get('/verify-email/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

 // 1️⃣ Vérifier la validité du lien
    if (! $request->hasValidSignature()) {
        // Si déjà vérifié → login
        if ($user->hasVerifiedEmail()) {
            return redirect('http://preprod.hellowap.com/Login?already_verified=1');
        }
        // Sinon → demander de renvoyer un mail
        return redirect()->away('http://preprod.hellowap.com/Confirm?email=' . urlencode($user->email));
    }

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Lien de vérification invalide'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return redirect('http://preprod.hellowap.com/Login?already_verified=1');
    }
    $user->markEmailAsVerified();

    return redirect('http://preprod.hellowap.com/Login?verified=1');
})->name('verification.verify');
// })->middleware('signed')->name('verification.verify');



Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/user', function () {
        return auth()->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');;
    Route::post('/register-client-step2', action: [AuthController::class, 'completeClientProfile']);

    // User routes
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
    Route::post('users/{user}/assign-permission', [UserController::class, 'assignPermission']);
    // Role routes
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions']);
    // Permission routes
    Route::apiResource('permissions', PermissionController::class);
    //intervenants
    Route::apiResource('intervenants', IntervenantController::class);
    // Categorie
    Route::apiResource('categorie', controller: CategorieController::class);
    Route::get('/categorieUser', [CategorieController::class, 'categorieUser'])->name('categorieUser');
    // vendors
    Route::apiResource('vendors', controller: VendorController::class);
    Route::post('/vendors/create-account', [VendorController::class, 'createAccountVendor']);
    //service
    Route::post('service/plan', [ServiceController::class, 'storeSimplePlan']); // Create simple plan in Kill Bill
    Route::apiResource('service', controller: ServiceController::class);

    Route::post('/service/{id}/toggle-archive', [ServiceController::class, 'toggleArchive']);



});
