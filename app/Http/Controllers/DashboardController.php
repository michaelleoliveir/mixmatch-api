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
        $time_range = $request->input('time_range');

        return response()->json([
            'profile' => $this->spotifyService->getUserData($spotifyToken),
            'tracks' => $this->spotifyService->getTopTracks($spotifyToken, $time_range),
            'artists' => $this->spotifyService->getTopArtists($spotifyToken, $time_range)
        ]);
    }
}
