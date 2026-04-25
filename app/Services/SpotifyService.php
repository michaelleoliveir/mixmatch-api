<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyService
{
    public function refreshAccessToken($user): ?string
    {
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $user->spotify_refresh_token,
            'client_id' => env('SPOTIFY_CLIENT_ID')
        ]);

        if($response->successful()) {
            $data = $response->json();

            $user->update([
                'spotify_token' => $data['access_token'],
                'spotify_refresh_token' => $data['refresh_token'] ?? $user->spotify_refresh_token,
                'spotify_token_expires_at' => now()->addSeconds($data['expires_in'])
            ]);

            return $data['access_token'];
        };

        return null;
    }

    public function getSpotifyUri(string $artist, string $track, string $token): string
    {        
        $query = "track:{$track} artist:{$artist}";
        $cacheResult = 'track_uri_' . md5(strtolower(trim("{$artist}{$track}")));
        $cachedUri = Cache::get($cacheResult);

        // se tiver a URI da música salvada em cache, ela é retornada
        if($cachedUri) {
            return $cachedUri;
        };

        // se não tiver, faz a busca na API do Spotify
        $response = Http::withToken($token)
            ->get('https://api.spotify.com/v1/search', [
                'q' => $query,
                'type' => 'track',
                'limit' => 1
            ]
        );

        $uri = $response->json()['tracks']['items'][0]['uri'] ?? '';

        if($uri) {
            Cache::put($cacheResult, $uri, now()->addDays(7));
        };

        return $uri;
    }

    public function createEmptyPlaylist(string $title, string $token): ?string
    {
        // criando a playlist
        $playlist = Http::withToken($token)
            ->post('https://api.spotify.com/v1/me/playlists', [
                'name' => $title,
                'description' => 'created by Mix&Match',
                'public' => false
            ]);

        if (!$playlist->successful())
            return null;

        return $playlist->json()['id'];
    }

    public function showTrackInfo(string $trackUri, string $token): array
    {
        $response = Http::withToken($token)
            ->get("https://api.spotify.com/v1/tracks//{$trackUri}");

        if (!$response->successful())
            return [];

        return $response->json();
    }

    public function addTracksToPlaylist(string $playlistId, string $token, array $songs)
    {
        $response = Http::withToken($token)
            ->post("https://api.spotify.com/v1/playlists/{$playlistId}/items", [
                'uris' => $songs
            ]);

        return $response;
    }
}