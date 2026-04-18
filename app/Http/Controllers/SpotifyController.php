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

    // public function createPlaylist(Request $request)
    // {
    //     $user = $request->user();

    //     $spotifyToken = $user->spotify_token;

    //     $recommendations = $this->geminiService->getMusicRecommendations($request->mood);

    //     $trackUris = collect($recommendations)->map(
    //         fn($item) =>
    //         $this->spotifyService->getSpotifyUri($item['artist'], $item['track'], $spotifyToken)
    //     )->filter()->toArray();
    // }
}
