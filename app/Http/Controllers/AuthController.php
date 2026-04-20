<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    public function loginWithSpotify()
    {
        return Socialite::driver('spotify')
            ->setScopes(['user-read-email', 'playlist-modify-public', 'playlist-modify-private'])
            ->stateless()
            ->with(['show_dialog' => 'true'])
            ->redirect();
    }

    public function handleSpotifyCallback(Request $request)
    {
        if(!$request->has('code') || $request->has('denied')) {
            return redirect()->to(env('FRONTEND_URL'));
        };

        try {
            $spotifyUser = Socialite::driver('spotify')->stateless()->user();

            $user = User::updateOrCreate(
                ['spotify_id' => $spotifyUser->getId()],
                [
                    'name' => $spotifyUser->getName(),
                    'email' => $spotifyUser->getEmail(),
                    'icon' => $spotifyUser->avatar,
                    'spotify_token' => $spotifyUser->token,
                    'spotify_refresh_token' => $spotifyUser->refreshToken,
                ]
            );

            $token = $user->createToken('spotify_token')->plainTextToken;

            return redirect()->to(env('FRONTEND_URL') . 'create-playlist?token=' . $token);

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->to(env('FRONTEND_URL') . 'error');
        }
    }
}
