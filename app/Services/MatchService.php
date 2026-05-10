<?php

namespace App\Services;

class MatchService
{
    /**
     * Create a new class instance.
     */

    protected $spotifyService;
    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    public function calculateMatch($owner, $visitor)
    {
        $ownerTopData = $owner->musicData()->get();
        $visitorTopData = $visitor->musicData()->get();

        if ($visitorTopData->isEmpty()) {
            $this->spotifyService->getTopArtists($visitor->spotify_token, 'medium_term');
            $this->spotifyService->getTopTracks($visitor->spotify_token, 'medium_term');

            $visitorTopData = $visitor->musicData()->get();
        }

        $ownerIds = $this->getTopArtistAndTrack($ownerTopData);
        $visitorIds = $this->getTopArtistAndTrack($visitorTopData);

        $matches = $this->compareData($ownerIds, $visitorIds);

        $formatedMatchingTracks = $this->formatDetails($matches['matching_tracks'], 'track', $ownerTopData);
        $formatedMatchingArtists = $this->formatDetails($matches['matching_artists'], 'artist', $ownerTopData);

        $totalPossible = count($ownerIds['top_artists']) + count($ownerIds['top_tracks']);
        $totalCommon = count($matches['matching_artists']) + count($matches['matching_tracks']);

        $score = $totalPossible > 0 ? ($totalCommon / $totalPossible) * 100 : 0;

        return [
            'match_percent' => round($score, 2),
            'owner_name' => $owner->name,
            'visitor_name' => $visitor->name,
            'tracks_match' => $formatedMatchingTracks,
            'artists_match' => $formatedMatchingArtists
        ];
    }

    private function getTopArtistAndTrack($data)
    {
        return [
            'top_tracks' => $data->where('type', 'track')->pluck('spotify_id')->toArray(),
            'top_artists' => $data->where('type', 'artist')->pluck('spotify_id')->toArray()
        ];
    }

    private function compareData($ownerIds, $visitorIds)
    {
        return [
            'matching_tracks' => array_intersect($ownerIds['top_tracks'], $visitorIds['top_tracks']),
            'matching_artists' => array_intersect($ownerIds['top_artists'], $visitorIds['top_artists'])
        ];
    }

    private function formatDetails($matchesId, $type, $collection)
    {
        return $collection->whereIn('spotify_id', $matchesId)
            ->where('type', $type)
            ->map(function ($item) use ($type) {
                $data = [
                    'name' => $item->name,
                    'photo' => $item->photo,
                ];

                if ($type === 'track') {
                    $data['artist_name'] = $item->artist_name;
                    $data['album'] = $item->album;
                }

                return $data;
            })->values()->toArray();
    }
}
