<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/auth/spotify/login', [AuthController::class, 'loginWithSpotify']);
Route::get('/auth/spotify/callback', [AuthController::class, 'handleSpotifyCallback']);

// validar se o usuário está logado e retornar o nome do usuário
Route::get('/validate-session', function (Request $request) {
    return response()->json([
        'user' => $request->user()->name,
        'icon' => $request->user()->icon,
    ]);
})->middleware('auth:sanctum');

// fazendo logout do usuário e removendo o token de validação
Route::post('/logout', function(Request $request) {
    $request->user()->currentAccessToken()->delete();
})->middleware('auth:sanctum');