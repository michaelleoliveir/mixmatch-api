<?php

namespace App\Services;

use App\Ai\Agents\PlaylistCurator;

class GeminiService
{
    public function getMusicRecommendations(string $mood): array
    {
        $response = (new PlaylistCurator)
            ->prompt("The user's current mood is: {$mood}");

        return json_decode($response->text);
    }
}
