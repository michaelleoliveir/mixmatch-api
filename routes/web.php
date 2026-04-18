<?php

use App\Services\GeminiService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-ia', function() {
    $service = new GeminiService();

    $result = $service->getMusicRecommendations('happy');

    dd($result);
});
