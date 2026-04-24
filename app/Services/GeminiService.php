<?php

namespace App\Services;

use App\Ai\Agents\PlaylistCurator;
use Illuminate\Support\Facades\Cache;

class GeminiService
{
    public function getMusicRecommendations(string $mood): array
    {
        $cacheKey = 'mood_playlist_' . md5(strtolower(trim($mood)));

        // 1. checa se a recomendação de músicas baseadas no humor já está em cache
        // 2. se já estiver, ele simplesmente retorna a recomendação de músicas
        // 3. se não, ele obtém a música através da IA e armazena por 24 horas no cache
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($mood) {
            try {
                $response = (new PlaylistCurator)
                    ->prompt("The user's current mood is: {$mood}");

                return json_decode($response->text, true);
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), '503') || str_contains($e->getMessage(), 'high demand')) {
                    abort(503, 'The recommendation service is currently experiencing high demand. Please try again later.');
                }

                abort(500, 'The recommendation service is currently unavailable. Please try again later.');
            }
        });
    }
}
