<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-ai/{mood}', function ($mood) {
    $gemini = app(App\Services\GeminiService::class);
    
    // Chama a IA
    $response = $gemini->getMusicRecommendations($mood);
    
    // Retorna o JSON formatado para você ler no navegador
    return response()->json([
        'mood_enviado' => $mood,
        'resposta_da_ia' => $response
    ]);
});

