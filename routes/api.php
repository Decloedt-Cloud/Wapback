<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use App\Models\User;


// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/register-client', [AuthController::class, 'registerClient']);
Route::post('/register-intervenant', [AuthController::class, 'registerIntervenant']);


Route::post('/forgot-password', [AuthController::class, 'sendResetEmail']);
Route::get('/reset-password', [AuthController::class, 'verifyResetLink'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Route::get('/verify-email/{id}/{hash}', function (Request $request, $id, $hash) {
//     $user = User::findOrFail($id);

//     if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
//         return response()->json(['message' => 'Lien de vérification invalide'], 403);
//     }

//     if ($user->hasVerifiedEmail()) {
//         return redirect('http://localhost:3000/login?already_verified=1');
//     }

//     $user->markEmailAsVerified();

//     return redirect('http://localhost:3000/login?verified=1');
// })->name('verification.verify');


Route::get('/verify-email/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);



    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Lien de vérification invalide'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return redirect('http://preprod.hellowap.com/Login?already_verified=1');
    }

    $user->markEmailAsVerified();

    return redirect('http://preprod.hellowap.com/Login?verified=1');
})->middleware('signed')->name('verification.verify');


Route::middleware('auth:sanctum')->group(function () {


    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');;

    // User routes
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
    Route::post('users/{user}/assign-permission', [UserController::class, 'assignPermission']);

    // Role routes
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions']);

    // Permission routes
    Route::apiResource('permissions', PermissionController::class);
});
