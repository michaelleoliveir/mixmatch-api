<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'matched_user_id', 'score', 'tracks_match', 'artists_match'])]
class MatchProfile extends Model
{
    protected $casts = [
        'tracks_match' => 'array',
        'artists_match' => 'array'
    ];

    protected $table = 'matches';
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function matchedUser()
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }
}
