<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SpotifyService
{
    public function getSpotifyUri(string $artist, string $track, string $token): string
    {
        $query = "track:{$track} artist:{$artist}";

        // enviando a query para a API do spotify para encontrar URI
        $response = Http::withToken($token)
            ->get('https://api.spotify.com/v1/search', [
                'q' => $query,
                'type' => 'track',
                'limit' => 1,
            ]);

        $data = $response->json();

        // caso não encontre a música
        if (!isset($data['tracks']['items'][0]['uri'])) {
            return '';
        }

        // caso encontrar a música
        return $data['tracks']['items'][0]['uri'];
    }

    public function createPlaylistFromUri(String $title, String $token): string
    {
        // criando a playlist
        $playlist = Http::withToken($token)
            ->post('https://api.spotify.com/v1/me/playlists', [
                'name' => $title,
                'description' => 'created by Mix&Match',
                'public' => false
            ]);
        
        if (!$playlist->successful()) return null;

        return $playlist->json()['id'];
    }
}