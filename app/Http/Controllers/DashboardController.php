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
        $time_range = $request->input('time_range');
        $dashboardData = $this->spotifyService->completeDashboardData($request->user(), $time_range);

        return response()->json($dashboardData);
    }
}
