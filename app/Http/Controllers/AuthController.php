<?php

namespace App\Http\Controllers;

use Http;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function connectionSpotifyAccount(Request $request)
    {
        $clientId = config('services.spotify.client_id');
        $secretId = config('services.spotify.client_secret');

        $response = Http::asForm()
            ->withBasicAuth($clientId, $secretId)
            ->post('https://accounts.spotify.com/api/token', ['grant_type' => 'client_credentials']);

        if($response->successful()) {
            return $response->json();
        }

        return $response->json();
    }

    public function loginWithSpotify() 
    {
        return Socialite::driver('spotify')->redirect();
    }

    public function handleSpotifyCallback()
    {
        $response = Socialite::driver('spotify')->user();

        dd($response);
    }
}
