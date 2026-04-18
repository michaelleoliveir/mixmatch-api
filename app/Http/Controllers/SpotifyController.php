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

    public function createPlaylist(Request $request)
    {
        $user = $request->user();
        $spotifyToken = $user->spotify_token;
        $mood = $request->input('mood', 'chill');

        // obtém o nome da playlist e as músicas que vão ser inseridas
        $recommendations = $this->geminiService->getMusicRecommendations($mood);

        // pegando as uris das músicas recomendadas pela IA
        $trackUris = collect($recommendations)->map(
            fn($item) =>
            $this->spotifyService->getSpotifyUri($item['artist'], $item['track'], $spotifyToken)
        )->filter()->values()->toArray();

        if (empty($trackUris)) {
            return response()->json(['error' => "We couldn't find the songs"], 404);
        }
        ;

        // criando a playlist
        $playlistId = $this->spotifyService->createPlaylistFromUri($recommendations[0]['playlist_title'], $spotifyToken);

        if (!$playlistId) {
            return response()->json(['error' => "Couldn't create the playlist"], 500);
        }
        ;

        // adicionando as músicas na playlist
        $this->spotifyService->addTracksToPlaylist($playlistId, $spotifyToken, $trackUris);

        return response()->json([
            'message' => 'Playlist successfully created',
            'playlist_id' => $playlistId,
            'playlist_name' => $aiData['playlist_name'] ?? "vibe: $mood",
        ]);
    }
}
