<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\FaceAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Face Authentication Routes - No face auth middleware here
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/face/auth', [FaceAuthenticationController::class, 'showFaceAuth'])
        ->name('face.auth');
    Route::post('/face/verify', [FaceAuthenticationController::class, 'verifyFace'])
        ->name('face.verify');
    Route::post('/face/register', [FaceAuthenticationController::class, 'registerFace'])
        ->name('face.register');
});

// Protected Routes - Apply face auth middleware
Route::middleware(['web', 'auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('face.auth')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('face.auth')
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('face.auth')
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('face.auth')
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
