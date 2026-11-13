<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoRatingController;

// Search videos (Put specific paths BEFORE wildcards)
Route::get('/videos/search', [VideoRatingController::class, 'videos']);

// Show all videos
Route::get('/videos', [VideoRatingController::class, 'videos']);

// Get ratings for one video
Route::get('/videos/{video}/ratings', [VideoRatingController::class, 'ratings']);

// Rating CRUD
Route::post('/ratings', [VideoRatingController::class, 'store']);
Route::put('/ratings/{rating}', [VideoRatingController::class, 'update']);
Route::delete('/ratings/{rating}', [VideoRatingController::class, 'destroy']);