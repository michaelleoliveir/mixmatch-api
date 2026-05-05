<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\SpotifyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ROTAS PÚBLICAS
Route::get('/auth/spotify/login', [AuthController::class, 'loginWithSpotify']);
Route::get('/auth/spotify/callback', [AuthController::class, 'handleSpotifyCallback']);

// ROTAS PROTEGIDAS
Route::middleware('auth:sanctum')->group(function () {

    // sessão e usuário
    Route::get('/validate-session', function (Request $request) {
        return response()->json([
            'user' => $request->user()->name,
            'icon' => $request->user()->icon,
        ]);
    });

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
    });

    // geração de playlist
    Route::get('/playlist/preview', [SpotifyController::class, 'getPreview']);
    Route::post('/playlist/create', [SpotifyController::class, 'createPlaylist']);

    Route::get('/dashboard', [DashboardController::class, 'getUserStats']);

    Route::get('/match', [MatchController::class, 'index']);
});