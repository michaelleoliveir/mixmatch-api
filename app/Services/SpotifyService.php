<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyService
{
    public function refreshAccessToken($user): ?string
    {
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $user->spotify_refresh_token,
            'client_id' => config('services.spotify.client_id')
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $user->update([
                'spotify_token' => $data['access_token'],
                'spotify_refresh_token' => $data['refresh_token'] ?? $user->spotify_refresh_token,
                'spotify_token_expires_at' => now()->addSeconds($data['expires_in'])
            ]);

            return $data['access_token'];
        }
        ;

        return null;
    }

    public function getRefreshToken($user)
    {
        if (now()->addMinutes(3)->greaterThanOrEqualTo($user->spotify_token_expires_at)) {
            $newSpotifyToken = $this->refreshAccessToken($user);

            return $newSpotifyToken ?? $user->spotify_token;
        }
        ;

        return $user->spotify_token;
    }

    public function getSpotifyUri(string $artist, string $track, string $token): string
    {
        $query = "track:{$track} artist:{$artist}";
        $cacheResult = 'track_uri_' . md5(strtolower(trim("{$artist}{$track}")));
        $cachedUri = Cache::get($cacheResult);

        // se tiver a URI da música salvada em cache, ela é retornada
        if ($cachedUri) {
            return $cachedUri;
        }
        ;

        // se não tiver, faz a busca na API do Spotify
        $response = Http::withToken($token)
            ->get(
                'https://api.spotify.com/v1/search',
                [
                    'q' => $query,
                    'type' => 'track',
                    'limit' => 1
                ]
            );

        $uri = $response->json()['tracks']['items'][0]['uri'] ?? '';

        if ($uri) {
            Cache::put($cacheResult, $uri, now()->addDays(7));
        }
        ;

        return $uri;
    }

    public function createEmptyPlaylist(string $title, string $token): ?array
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

        $data = $playlist->json();

        return [
            'id' => $data['id'],
            'external_url' => $data['external_urls']['spotify'],
        ];
    }

    public function showTrackInfo(string $trackUri, string $token): array
    {
        $cacheKey = 'track_info_' . md5(strtolower(trim($trackUri)));

        return Cache::remember($cacheKey, now()->addDays(6), function () use ($trackUri, $token) {
            $response = Http::withToken($token)
                ->get("https://api.spotify.com/v1/tracks//{$trackUri}");

            if (!$response->successful())
                return [];

            $data = $response->json();

            return [
                'name' => $data['name'],
                'artist' => $data['artists'][0]['name'] ?? 'Unknown Artist',
                'album' => $data['album']['images'][0]['url'] ?? null
            ];

        });
    }

    public function addTracksToPlaylist(string $playlistId, string $token, array $songs)
    {
        $response = Http::withToken($token)
            ->post("https://api.spotify.com/v1/playlists/{$playlistId}/items", [
                'uris' => $songs
            ]);

        return $response;
    }

    public function getUserData(string $token): ?array
    {
        $response = Http::withToken($token)
            ->get("https://api.spotify.com/v1/me");

        if ($response->failed()) {
            throw new Exception('Error fetching user data: ' . $response->body());
        }

        $data = $response->json();

        return [
            'display_name' => $data['display_name'],
            'email' => $data['email'] ?? null,
            'followers' => $data['followers']['total'] ?? 0,
            'icon' => !empty($data['images']) ? $data['images'][0]['url'] : null
        ];
    }

    public function getTopArtists(string $token, string $time_range): ?array
    {
        $response = Http::withToken($token)
            ->get("https://api.spotify.com/v1/me/top/artists", [
                'time_range' => $time_range,
                'limit' => 10
            ]);

        if ($response->failed()) {
            throw new Exception('Error fetching top artists: ' . $response->body());
        }

        $data = $response->json();

        return [
            'artists' => collect($data['items'])->map(fn($artist) => [
                'name' => $artist['name'],
                'id' => $artist['id'],
                'image' => $artist['images'][0]['url'] ?? null,
            ])->all()
        ];
    }

    public function getTopTracks(string $token, string $time_range): ?array
    {
        $response = Http::withToken($token)
            ->get("https://api.spotify.com/v1/me/top/tracks", [
                'time_range' => $time_range,
                'limit' => 10
            ]);

        if ($response->failed()) {
            throw new Exception('Error fetching top tracks: ' . $response->body());
        }

        $data = $response->json();

        return [
            'tracks' => collect($data['items'])->map(fn($track) => [
                'name' => $track['name'],
                'artist' => $track['artists'][0]['name'],
                'album_name' => $track['album']['name'],
                'album_cover' => $track['album']['images'][0]['url'] ?? null,
                'explicit' => $track['explicit']
            ])->all()
        ];
    }
}