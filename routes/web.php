<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoRatingController;
use Illuminate\Support\Facades\Route;


// Home
Route::get('/', function () {
    return redirect()->route('login');
});


// Guest Routes
Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);

    // Register
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);
});


// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/video-ratings', [
    VideoRatingController::class,
    'index'
])->name('video-ratings.index');
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});