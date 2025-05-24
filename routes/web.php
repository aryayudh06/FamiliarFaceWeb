<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\FaceAuthenticationController;
use App\Http\Controllers\Register2FAController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Guest routes (login/register)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
});

// Face Authentication Routes - After login but before dashboard
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/face/auth', [FaceAuthenticationController::class, 'showFaceAuth'])
        ->name('face.auth');
    Route::post('/face/verify', [FaceAuthenticationController::class, 'verifyFace'])
        ->name('face.verify');
    Route::post('/face/register', [FaceAuthenticationController::class, 'registerFace'])
        ->name('face.register');
});

// Protected Routes - Require both auth and face verification
Route::middleware(['web', 'auth', 'verified', \App\Http\Middleware\RequireFaceAuth::class])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // 2FA User Management Routes
    Route::get('/2fa-users', [Register2FAController::class, 'index'])->name('2fa.index');
    Route::get('/2fa-users/create', [Register2FAController::class, 'create'])->name('2fa.create');
    Route::post('/2fa-users', [Register2FAController::class, 'store'])->name('2fa.store');
    Route::delete('/2fa-users/{register2FA}', [Register2FAController::class, 'destroy'])->name('2fa.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
