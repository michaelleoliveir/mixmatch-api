<?php

namespace App\Http\Controllers;

use App\Models\MatchProfile;
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

        MatchProfile::updateOrCreate(
            [
                'user_id' => $owner->id,
                'matched_user_id' => $visitor->id,
            ],
            [
                'score' => $result['match_percent'],
                'tracks_match' => $result['tracks_match'],
                'artists_match' => $result['artists_match']
            ]
        );

        return response()->json($result);
    }

    public function ranking()
    {
        $user = Auth::user();

        $matches = MatchProfile::where('user_id', $user->id)
            ->orWhere('matched_user_id', $user->id)
            ->orderBy('score', 'desc')
            ->with(['user', 'matchedUser'])
            ->get()
            ->map(function ($match) use ($user) {
                $other = $match->user_id === $user->id
                    ? $match->matchedUser
                    : $match->user;
                return [
                    'id' => $match->id,
                    'user' => ['name' => $other->name, 'icon' => $other->icon],
                    'score' => $match->score,
                    'registered_at' => $match->created_at
                ];
            });

        return response()->json([
            'ranking' => $matches
        ]);
    }

    public function rankingDetails($match_id)
    {
        $match = MatchProfile::with(['user', 'matchedUser'])->findOrFail($match_id);

        return response()->json([
            'score' => $match->score,
            'date' => $match->updated_at,
            'tracks_match' => $match->tracks_match,
            'artists_match' => $match->artists_match,
            'owner' => ['name' => $match->user->name, 'icon' => $match->user->icon],
            'visitor' => ['name' => $match->matchedUser->name, 'icon' => $match->matchedUser->icon]
        ]);
    }
}
