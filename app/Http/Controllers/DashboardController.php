<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    public function getUserStats(Request $request)
    {
        $user = $request->user();
        $spotifyToken = $this->spotifyService->getRefreshToken($user);

        return response()->json([
            'profile' => $this->spotifyService->getUserData($spotifyToken),
            'tracks' => $this->spotifyService->getTopTracks($spotifyToken),
            'artists' => $this->spotifyService->getTopArtists($spotifyToken)
        ]);
    }
}
