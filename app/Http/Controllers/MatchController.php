<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MatchController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if(!$user->match_code) {
            $user->update([
                'match_code' => Str::random(10)
            ]);
        };

        $sharedLink = url("/match/{$user->match_code}");

        return response()->json([
            'shared_link' => $sharedLink,
            'match_code' => $user->match_code
        ]);
    }
}
