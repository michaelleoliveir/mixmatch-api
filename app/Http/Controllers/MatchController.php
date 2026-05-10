<?php

namespace App\Http\Controllers;

use App\Services\MatchService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\SpotifyService;

class MatchController extends Controller
{
    protected $spotifyService;
    protected $matchService;

    public function __construct(SpotifyService $spotifyService, MatchService $matchService)
    {
        $this->spotifyService = $spotifyService;
        $this->matchService = $matchService;
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->match_code) {
            $user->update([
                'match_code' => Str::random(10)
            ]);
        }
        ;

        $sharedLink = url(config('services.spotify.redirect_front') . "match/{$user->match_code}");

        return response()->json([
            'shared_link' => $sharedLink,
            'match_code' => $user->match_code
        ]);
    }

    public function matchStats($match_code)
    {
        $owner = User::where('match_code', $match_code)->firstOrFail();
        $visitor = Auth::user();

        if ($owner->id === $visitor->id) {
            return response()->json([
                'message' => 'You cannot compare profiles with yourself.'
            ], 400);
        }

        $result = $this->matchService->calculateMatch($owner, $visitor);

        return response()->json($result);
    }
}
