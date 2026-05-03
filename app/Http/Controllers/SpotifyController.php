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
        $spotifyToken = $this->spotifyService->getRefreshToken($request->user());
        $mood = $request->input('mood', 'chill');

        // obtém o nome da playlist e as músicas que vão ser inseridas
        $recommendations = $this->geminiService->getMusicRecommendations($mood);

        // obtém as músicas que a IA recomendou
        $suggestions = $recommendations['tracks'] ?? [];

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
        $token = $request->user()->spotify_token;
        $playlistName = $request->input('playlist_name');
        $uris = $request->input('uris', []);

        $playlistId = $this->spotifyService->createEmptyPlaylist($playlistName, $token);

        if (!$playlistId) {
            return response()->json(['error' => 'Failed to create playlist'], 500);
        }
        ;

        $this->spotifyService->addTracksToPlaylist($playlistId['id'], $token, $uris);

        return response()->json([
            'message' => 'Playlist created successfully',
            'playlist_url' => $playlistId['external_url'],
        ]);
    }
}
