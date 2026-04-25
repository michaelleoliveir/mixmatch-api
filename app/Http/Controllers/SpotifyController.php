<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\SpotifyService;
use Illuminate\Http\Request;

class SpotifyController extends Controller
{
    protected $geminiService;
    protected $spotifyService;

    public function __construct(GeminiService $geminiService, SpotifyService $spotifyService)
    {
        $this->geminiService = $geminiService;
        $this->spotifyService = $spotifyService;
    }

    public function getPreview(Request $request)
    {
        $user = $request->user();
        $spotifyToken = $user->spotify_token;
        $mood = $request->input('mood', 'chill');

        // obtém o nome da playlist e as músicas que vão ser inseridas
        $recommendations = $this->geminiService->getMusicRecommendations($mood);

        // obtém as músicas que a IA recomendou
        $suggestions = $recommendations['tracks'] ?? [];

        // verificando se o token do usuário ainda continua válido
        // se não, ele é atualizado
        if (now()->addMinutes(3)->greaterThanOrEqualTo($user->spotify_token_expires_at)) {
            $newSpotifyToken = $this->spotifyService->refreshAccessToken($user);

            if ($newSpotifyToken) {
                $spotifyToken = $newSpotifyToken;
            };
        };

        // pegando as informações das músicas sugeridas pela IA
        $previewTracks = collect($suggestions)->map(function ($track) use ($spotifyToken) {

            // pegando a URI da música
            $uri = $this->spotifyService->getSpotifyUri($track['artist'], $track['title'], $spotifyToken);

            if (!$uri)
                return null;

            // pegando somente o ID da música
            $trackId = str_replace('spotify:track:', '', $uri);
            $trackDetails = $this->spotifyService->showTrackInfo($trackId, $spotifyToken);

            return [
                'uri' => $uri,
                'artist' => $trackDetails['artist'],
                'name' => $trackDetails['name'],
                'album_image' => $trackDetails['album'],
            ];
        })->filter()->values()->toArray();


        return response()->json([
            'playlist_name' => $recommendations['playlist_name'],
            'tracks' => $previewTracks
        ]);
    }

    public function createPlaylist(Request $request)
    {
        $user = $request->user();
        $token = $user->spotify_token;
        $playlistName = $request->input('playlist_name');
        $uris = $request->input('uris', []);

        $playlistId = $this->spotifyService->createEmptyPlaylist($playlistName, $token);

        if (!$playlistId) {
            return response()->json(['error' => 'Failed to create playlist'], 500);
        }
        ;

        $this->spotifyService->addTracksToPlaylist($playlistId, $token, $uris);

        return response()->json([
            'message' => 'Playlist created successfully',
            'playlist_id' => $playlistId
        ]);
    }
}
