<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\SpotifyService;

class MatchController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->match_code) {
            $user->update([
                'match_code' => Str::random(10)
            ]);
        };

        $sharedLink = url(config('services.spotify.redirect_front') . "match/{$user->match_code}");

        return response()->json([
            'shared_link' => $sharedLink,
            'match_code' => $user->match_code
        ]);
    }

    public function matchStats($match_code)
    {
        // 1. verificar se o usuário logado é o mesmo que compartilhou o link
        $owner = User::where('match_code', $match_code)->firstOrFail();
        $visitor = Auth::user();

        // 2. se for, lançar mensagem de erro
        if ($owner->id === $visitor->id) {
            return response()->json([
                'message' => 'You cannot compare profiles with yourself.'
            ], 400);
        }

        // 3. pegar os Tops do dono e visitante
        $ownerTopData = $owner->musicData()->get();
        $visitorTopData = $visitor->musicData()->get();

        if (empty($visitorTopData)) {
            $this->spotifyService->getTopArtists($visitor->spotify_token, 'medium_term');
            $this->spotifyService->getTopTracks($visitor->spotify_token, 'medium_term');

            $visitorTopData = $visitor->musicData()->get();
        };

        // 4. obtendo o ID das músicas/artistas
        $ownerTopTracks = $ownerTopData->where('type', 'track')->pluck('spotify_id')->toArray();
        $ownerTopArtists = $ownerTopData->where('type', 'artist')->pluck('spotify_id')->toArray();

        $visitorTopTracks = $ownerTopData->where('type', 'track')->pluck('spotify_id')->toArray();
        $visitorTopArtists = $ownerTopData->where('type', 'artist')->pluck('spotify_id')->toArray();

        $matchingArtists = array_intersect($ownerTopArtists, $visitorTopArtists);
        $matchingTracks = array_intersect($ownerTopTracks, $visitorTopTracks);

        $totalPossible = count($ownerTopArtists) + count($ownerTopTracks);
        $totalCommon = count($matchingArtists) + count($matchingTracks);

        $score = $totalPossible > 0 ? ($totalCommon / $totalPossible) * 100 : 0;

        return response()->json([
            'match_percent' => round($score, 2),
            'owner_name' => $owner->name,
            'visitor_name' => $visitor->name
        ]);
    }
}
