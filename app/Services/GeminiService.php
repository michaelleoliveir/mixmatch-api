<?php

namespace App\Services;

use App\Ai\Agents\PlaylistCurator;

class GeminiService
{
    public function getMusicRecommendations(string $mood): array
    {
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
    }
}
