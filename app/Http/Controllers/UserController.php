<?php

namespace App\Http\Controllers;

use App\Models\User;
use Http;
use Illuminate\Http\Request;

class UserController extends Controller
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
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
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
}
