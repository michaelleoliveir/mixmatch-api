<?php

namespace App\Console\Commands;

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
    public function handle()
    {
        //
    }
}
