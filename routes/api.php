<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// middleware essencial para aplicações web => CSRF, gerenciamento de sessão, criptografia
Route::group(['middleware' => ['web']], function () {
    Route::get('/auth/spotify/login', [AuthController::class, 'loginWithSpotify']);
    Route::get('/auth/spotify/callback', [AuthController::class, 'handleSpotifyCallback']);
});

Route::post('/connection-spotify-account', [UserController::class, 'connectionSpotifyAccount']);