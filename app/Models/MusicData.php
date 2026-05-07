<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'type', 'spotify_id', 'ranking'])]
class MusicData extends Model
{
    protected $table = 'user_music_data';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
