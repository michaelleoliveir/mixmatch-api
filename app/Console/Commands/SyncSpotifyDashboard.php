<?php

namespace App\Console\Commands;

use App\Services\SpotifyService;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-spotify-dashboard')]
#[Description('Sync spotify data')]
class SyncSpotifyDashboard extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SpotifyService $spotifyService)
    {
        $users = User::whereNotNull('spotify_refresh_token')->get();
        $ranges = ['short_term', 'medium_term', 'long_term'];

        foreach ($users as $user) {
            $this->info("Sincronizando dados do usuário {$user->name}");

            try {
                foreach ($ranges as $range) {
                    $spotifyService->completeDashboardData($user, $range);

                    $this->info("Dados {$range} sincronizados com sucesso");
                }
            } catch (\Exception $e) {
                $this->error("Falha ao sincronizar {$user->name}");

                if(str_contains($e->getMessage(), '401')) {
                    $user->update([
                        'spotify_refresh_token' => null,
                        'spotify_token' => null
                    ]);

                    $this->info("Tokens de {$user->name} foram removidos por estarem expirados");
                }
                continue;
            }
        }
    }
}
