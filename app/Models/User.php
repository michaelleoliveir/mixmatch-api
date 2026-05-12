<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'icon', 'spotify_id', 'spotify_token', 'spotify_refresh_token', 'spotify_token_expires_at', 'match_code'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'spotify_token_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function myLinkMatches()
    {
        return $this->hasMany(MatchProfile::class, 'user_id');
    }

    public function matchesWithOthers()
    {
        return $this->hasMany(MatchProfile::class, 'matched_user_id');
    }

    public function musicData()
    {
        return $this->hasMany(MusicData::class, 'user_id');
    }
}
