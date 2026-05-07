<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;

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

    public function matchStats(Request $request, $match_code)
    {
        // 1. verificar se o usuário logado é o mesmo que compartilhou o link
        $user = Auth::user()->id;
        $match_code_user = User::where('match_code', $match_code)->value('id');

        // 2. se for, lançar mensagem de erro
        if($user === $match_code_user) {
            return response()->json([
                'message' => 'You cannot compare profiles with yourself.'
            ], 400);
        }
    }
}
